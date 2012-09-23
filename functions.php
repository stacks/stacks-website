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
 $text = str_replace("\`e", "&egrave;", $text);
 $text = str_replace("{\\v C}", "&#268;", $text);
 // this is to remedy a bug in import_titles
 $text = str_replace("\\v C}", "&#268;", $text);

 return $text;
}

function parse_preview($preview) {
  // remove irrelevant new lines at the end
  $preview = trim($preview);
  // escape stuff
  $preview = htmlentities($preview);
  // but links should work: tag links are made up from alphanumeric characters, slashes, dashes and underscores, while the LaTeX label contains only alphanumeric characters and dashes
  $preview = preg_replace('/&lt;a href=&quot;([A-Za-z0-9\/-_]+)&quot;&gt;([A-Za-z0-9\-]+)&lt;\/a&gt;/', '<a href="' . full_url('') . '$1">$2</a>', $preview);

  return $preview;
}

function parse_references($string) {
  // look for \ref before MathJax can and see if they point to existing tags
  $references = array();

  preg_match_all('/\\\ref{[\w-]*}/', $string, $references);
  foreach ($references[0] as $reference) {
    // get the label or tag we're referring to, nothing more
    $target = substr($reference, 5, -1);

    // we're referring to a tag
    if (is_valid_tag($target)) {
      // regardless of whether the tag exists we insert the link, the user is responsible for meaningful content
      $string = str_replace($reference, '[`' . $target . '`](' . full_url('tag/' . $target) . ')', $string);
    }
    // the user might be referring to a label
    else {
      // might it be that he is referring to a "local" label, i.e. in the same chapter as the tag?
      if (!label_exists($target)) {
        $label = get_label(strtoupper($_GET['tag']));
        $parts = explode('-', $label);
        // let's try it with the current chapter in front of the label
        $target = $parts[0] . '-' . $target;
      }

      // the label (potentially modified) exists in the database (and it is active), so the user is probably referring to it
      // if he declared a \label{} in his string with this particular label value he's out of luck
      if (label_exists($target)) {
        $tag = get_tag_referring_to($target);
        $string = str_replace($reference, '[`' . $tag . '`](' . full_url('tag/' . $tag) . ')', $string);
      }
    }
  }

  return $string;
}

function parse_latex($code) {
  // remove labels
  $code = preg_replace("/\\\label\{.*\}/", "", $code);

  // all big environments with their corresponding markup
  $code = str_replace("\\begin{lemma}\n", "<strong>Lemma</strong> <em>", $code);
  $code = str_replace("\\end{lemma}\n", "</em></p>", $code);

  // proof environment
  $code = str_replace("\\begin{proof}\n", "<p><strong>Proof</strong> ", $code);
  $code = str_replace("\\end{proof}\n", "$\square$</p>", $code);

  /**
   * TODO
   * - {\it ...}
   * ``
   * better reference parsing
   * better environment parsing
   * \section etc.
   * \square in right corner of proof
   */

  // enumerates etc.
  $code = str_replace("\\begin{enumerate}\n", "<ol>", $code);
  $code = str_replace("\\end{enumerate}\n", "</ol>", $code);
  $code = str_replace("\\item", "<li>", $code);

  // let HTML be aware of paragraphs
  $code = str_replace("\n\n", "</p><p>", $code);

  // parse references
  $code = preg_replace('/\\\ref\{(.*)\}/', "$1", $code);

  // remove \medskip and \noindent
  $code = str_replace("\\medskip", "", $code);
  $code = str_replace("\\noindent", "", $code);

  // fix macros
  $macros = array(
    // TODO check \mathop in output
    "\\lim" => "\mathop{\\rm lim}\\nolimits",
    "\\colim" => "\mathop{\\rm colim}\\nolimits",
    "\\Spec" => "\mathop{\\rm Spec}",
    "\\Hom" => "\mathop{\\rm Hom}\\nolimits",
    "\\SheafHom" => "\mathop{\mathcal{H}\!{\it om}}\nolimits",
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

function print_tag_code_and_preview($code) {
  print("    <pre class='tag-preview-code'>\n" . parse_preview($code) . "\n    </pre>\n");
  print("    <blockquote class='tag-preview-'>\n" . parse_latex($code) . "</blockquote>\n");
}
?>
