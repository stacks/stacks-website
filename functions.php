<?php

function is_valid_tag($tag) {
  return (bool) preg_match_all('/^[[:alnum:]]{4}$/', $tag, $matches) == 1;
}

function tag_exists($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT COUNT(*) FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function label_exists($label) {
  global $db;
  try {
    $sql = $db->prepare('SELECT COUNT(*) FROM tags WHERE label = :label');
    $sql->bindParam(':label', $label);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function position_exists($position) {
  global $db;
  try {
    $sql = $db->prepare('SELECT COUNT(*) FROM tags WHERE position = :position AND active = "TRUE"');
    $sql->bindParam(':position', $position);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function tag_is_active($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT active FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute())
      return $sql->fetchColumn() == 'TRUE';
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function get_label($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT label FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute())
      return $sql->fetchColumn();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "";
}

function get_id($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT book_id FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute())
      return $sql->fetchColumn();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "";
}

function get_tag($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag, label, file, chapter_page, book_page, book_id, value, name, type, position FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute()) {
      // return first (= only) row of the result
      while ($row = $sql->fetch()) return $row;
    }
    return null;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function get_tag_at($position) {
  assert(position_exists($position));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag, label FROM tags WHERE position = :position AND active = "TRUE"');
    $sql->bindParam(':position', $position);

    if ($sql->execute())
      return $sql->fetch();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "ZZZZ";
}

function get_tag_with_id($id) {
  global $db;
  try {
    $sql = $db->prepare('SELECT tag FROM tags WHERE book_id = :id AND active = "TRUE"');
    $sql->bindParam(':id', $id);

    if ($sql->execute())
      return $sql->fetch();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "ZZZZ";
}

function get_tag_referring_to($label) {
  assert(label_exists($label));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag FROM tags WHERE label = :label AND active = "TRUE"');
    $sql->bindParam(':label', $label);

    if ($sql->execute())
      return $sql->fetchColumn();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "ZZZZ";
}

function latex_to_html($text) {
 $text = str_replace("\'E", "&Eacute;", $text);
 $text = str_replace("\'e", "&eacute;", $text);
 // TODO more accents
 $text = str_replace("\"o", "&ouml;", $text);
 $text = str_replace("\`e", "&egrave;", $text);
 $text = str_replace("{\\v C}", "&#268;", $text);
 $text = str_replace("``", "\"", $text);
 $text = str_replace("''", "\"", $text);
 $text = str_replace("\\ ", " ", $text);
 // this is to remedy a bug in import_titles
 $text = str_replace("\\v C}", "&#268;", $text);

 return $text;
}

function parse_preview($preview) {
  // remove irrelevant new lines at the end
  $preview = trim($preview);
  // escape stuff
  $preview = htmlentities($preview);

  // don't escape in $$ ... $$ because XyJax doesn't like that
  $parts = explode('$$', $preview);
  for ($i = 0; $i < sizeof($parts); $i++) {
    if ($i % 2 == 1) {
      $parts[$i] = str_replace('&gt;', '>', $parts[$i]);
      $parts[$i] = str_replace('&lt;', '<', $parts[$i]);
      $parts[$i] = str_replace('&amp;', '&', $parts[$i]);
    }
  }
  $preview = implode('$$', $parts);

  // but links should work: tag links are made up from alphanumeric characters, slashes, dashes and underscores, while the LaTeX label contains only alphanumeric characters and dashes
  $preview = preg_replace('/&lt;a href=&quot;\/([A-Za-z0-9\/-_]+)&quot;&gt;([A-Za-z0-9\-]+)&lt;\/a&gt;/', '<a href="' . full_url('') . '$1">$2</a>', $preview);


  return $preview;
}

function parse_latex($tag, $code) {
  // get rid of things that should be HTML
  $code = parse_preview($code);

  // this is the regex for all (sufficiently nice) text that can occur in things like \emph
  $regex = "[\w\s$,.:()'-\\\\$]+";

  // remove labels
  $code = preg_replace("/\\\label\{.*\}\n/", "", $code);

  // all big environments with their corresponding markup
  $environments = array(
    "lemma" => array("Lemma", true),
    "definition" => array("Definition", false),
    "remark" => array("Remark", false),
    "remarks" => array("Remarks", false),
    "example" => array("Example", false),
    "theorem" => array("Theorem", true),
    "exercise" => array("Exercise", false),
    "situation" => array("Situation", false),
    "proposition" => array("Proposition", true)
  );
  foreach ($environments as $environment => $information) {
    $code = str_replace("\\begin{" . $environment . "}\n", "<strong>" . $information[0] . "</strong> " . ($information[1] ? '<em>' : ''), $code);
    $code = preg_replace("/\\\begin{" . $environment . "}\[(.*)\]/", "<strong>" . $information[0] . "</strong> ($1) " . ($information[1] ? '<em>' : ''), $code);
    $code = str_replace("\\end{" . $environment . "}", ($information[1] ? '</em>' : '') . "</p>", $code);
  }

  // these do not fit into the system above
  $code = str_replace("\\begin{center}\n", "<center>", $code);
  $code = str_replace("\\end{center}", "</center>", $code);
  
  $code = str_replace("\\begin{quote}", "<blockquote>", $code);
  $code = str_replace("\\end{quote}", "</blockquote>", $code);

  // proof environment
  $code = str_replace("\\begin{proof}\n", "<p><strong>Proof</strong> ", $code);
  $code = preg_replace("/\\\begin\{proof\}\[(" . $regex . ")\]/", "<p><strong>$1</strong> ", $code);
  $code = str_replace("\\end{proof}", "</p><p style='text-align: right;'>$\square$</p>", $code);

  // sections etc.
  $code = preg_replace("/\\\section\{(" . $regex . ")\}/", "<h3>$1</h3>", $code);
  $code = preg_replace("/\\\subsection\{(" . $regex . ")\}/", "<h4>$1</h4>", $code);

  // hyperlinks
  $code = preg_replace("/\\\href\{(.*)\}\{(" . $regex . ")\}/", "<a href=\"$1\">$2</a>", $code);
  $code = preg_replace("/\\\url\{(.*)\}/", "<a href=\"$1\">$1</a>", $code);

  // emphasis
  $code = preg_replace("/\{\\\it (" . $regex . ")\}/", "<em>$1</em>", $code);
  $code = preg_replace("/\{\\\em (" . $regex . ")\}/", "<em>$1</em>", $code);
  $code = preg_replace("/\\\emph\{(" . $regex . ")\}/", "<em>$1</em>", $code);

  // footnotes
  $code = preg_replace("/\\\\footnote\{(" . $regex . ")\}/", " ($1)", $code);

  // handle citations
  $code = preg_replace("/\\\cite\{([\w-]*)\}/", "[$1]", $code);
  $code = preg_replace("/\\\cite\[(" . $regex . ")\]\{([\w-]*)\}/", "[$2, $1]", $code);

  // filter \input{chapters}
  $code = str_replace("\\input{chapters}", "", $code);

  // fix special characters
  $code = latex_to_html($code);

  // enumerates etc.
  $code = str_replace("\\begin{enumerate}\n", "<ol>", $code);
  $code = str_replace("\\end{enumerate}\n", "</ol>", $code);
  $code = str_replace("\\begin{itemize}\n", "<ul>", $code);
  $code = str_replace("\\end{itemize}\n", "</ul>", $code);
  $code = preg_replace("/\\\begin{list}(.*)\n/", "<ul>", $code); // unfortunately I have to ignore information in here
  $code = str_replace("\\end{list}", "</ul>", $code);
  $code = preg_replace("/\\\item\[(.*)\]/", "<li>", $code);
  $code = str_replace("\\item", "<li>", $code);

  // let HTML be aware of paragraphs
  $code = str_replace("\n\n", "</p><p>", $code);
  $code = str_replace("\\smallskip", "", $code);
  $code = str_replace("\\medskip", "", $code);
  $code = str_replace("\\noindent", "", $code);

  // parse references
  //$code = preg_replace('/\\\ref\{(.*)\}/', "$1", $code);
  $references = array();
  
  preg_match_all('/\\\ref{<a href=\"([\w\/]+)\">([\w-]+)<\/a>}/', $code, $references);
  for ($i = 0; $i < count($references[0]); ++$i) {
    $code = str_replace($references[0][$i], "<a href='" . $references[1][$i] . "'>" . get_id(substr($references[1][$i], -4, 4)) . "</a>", $code);
  }

  // fix macros
  $macros = array(
    // TODO check \mathop in output
    "\\lim" => "\mathop{\\rm lim}\\nolimits",
    "\\colim" => "\mathop{\\rm colim}\\nolimits",
    "\\Spec" => "\mathop{\\rm Spec}",
    "\\Hom" => "\mathop{\\rm Hom}\\nolimits",
    "\\SheafHom" => "\mathop{\mathcal{H}\!{\it om}}\\nolimits",
    "\\Sch" => "\\textit{Sch}",
    "\\Mor" => "\mathop{\\rm Mor}\\nolimits",
    "\\Ob" => "\mathop{\\rm Ob}\\nolimits",
    "\\Sh" => "\mathop{\\textit{Sh}}\\nolimits");
  $code = str_replace(array_keys($macros), array_values($macros), $code);

  return $code;
}

function print_navigation() {
?>
    <ul id="navigation">
      <li><a href="<?php print(full_url('')); ?>">index</a>
      <li><a href="<?php print(full_url('about')); ?>">about</a>
      <li><a href="<?php print(full_url('tags')); ?>">tags explained</a>
      <li><a href="<?php print(full_url('tag')); ?>">tag lookup</a>
      <li><a href="<?php print(full_url('browse')); ?>">browse</a>
      <li><a href="<?php print(full_url('search')); ?>">search</a>
      <li><a href="<?php print(full_url('recent-comments')); ?>">recent comments</a>
      <li><a href="http://math.columbia.edu/~dejong/wordpress/">blog</a>
    </ul>
    <br style="clear: both;">
<?php
}

function print_tag_code_and_preview($tag, $code) {
  print("<p class='view-code-toggle' id='tag-preview-code-" . $tag . "-link'><a href='#tag-preview-output-" . $tag . "'>view</a></p>");
  print("<pre class='tag-preview-code' id='tag-preview-code-" . $tag . "'>\n" . parse_preview($code) . "\n    </pre>\n");

  print("<p class='view-code-toggle' id='tag-preview-output-" . $tag . "-link'><a href='#tag-preview-code-" . $tag . "'>code</a></p>");
  print("<blockquote class='tag-preview-output' id='tag-preview-output-" . $tag . "'>\n" . parse_latex($tag, $code) . "</blockquote>\n");

?>
  <script type="text/javascript">
    $(document).ready(function() {
      // hide preview
      $("#tag-preview-output-<?php print($tag); ?>").toggle();
      $("#tag-preview-output-<?php print($tag); ?>-link").toggle();
    });

    function toggle_preview_output(e) {
      // prevent movement
      e.preventDefault();

      $("#tag-preview-output-<?php print($tag); ?>, #tag-preview-output-<?php print($tag); ?>-link").toggle();
      $("#tag-preview-code-<?php print($tag); ?>, #tag-preview-code-<?php print($tag); ?>-link").toggle();
    }
   
    $("#tag-preview-code-<?php print($tag); ?>-link a").click(toggle_preview_output);
    $("#tag-preview-output-<?php print($tag); ?>-link a").click(toggle_preview_output);

  </script>
<?php
}
?>
