<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/tags.php");

/**
 * The list of possible types:
 * - definition
 * - equation
 * - example
 * - exercise
 * - item
 * - lemma
 * - proposition
 * - remark
 * - remarks
 * - section
 * - situation
 * - subsection
 * - theorem
 */

function getChapterFromID($id) {
  $parts = explode(".", $id);
  return $parts[0];
}

function isPhantom($label) {
  return substr_compare($label, "section-phantom", -15, 15) == 0;
}

function getPart($id) {
  global $parts, $database;

  $sql = $database->prepare("SELECT title FROM sections WHERE number NOT LIKE '%.%' ORDER BY CAST(number AS INTEGER)");

  $part = "Preliminaries";

  if ($sql->execute()) {
    $chapters = $sql->fetchAll();

    for ($i = 0; $i < sizeof($chapters); $i++) {
      if ($i == $id)
        return $part;

      if (in_array($chapters[$i]["title"], array_keys($parts)))
        $part = $parts[$chapters[$i]["title"]];
    }
  }
}

function parseComment($comment) {
  // parse \ref{}, but only when the line is not inside a code fragment
  $lines = explode("\n", $comment);
  foreach ($lines as &$line) {
    // check whether the line is a code fragment or not
    if (substr($line, 0, 4) != '    ')
      $line = parseReferences($line);
  }
  $comment = implode($lines, "\n");

  // fix underscores and asterisks (all underscores in math mode will be escaped
  $result = '';
  $mathmode = false;
  foreach (str_split($comment) as $position => $character) {
    // match math mode (\begin{equation}\end{equation} goes fine mysteriously)
    if ($character == "$") {
      // handle $$ correctly
      if ($position + 1 < strlen($comment) && $comment[$position + 1] != "$")
        $mathmode = !$mathmode;
    }

    // replace unescaped underscores in math mode, the accessed position always exists because we had to enter math mode first
    if ($mathmode && $character == "_" && $comment[$position - 1] != "\\")
      $result .= "\\_";
    elseif ($mathmode && $character == "*" && $comment[$position - 1] != "\\")
      $result .= "\\*";
    else
      $result .= $character;
  }
  $comment = $result;
  // remove <>&"'
  $comment = htmlspecialchars($comment);
  // duplicate double backslashes
  $comment = str_replace("\\\\", "\\\\\\\\", $comment);
  // apply Markdown (i.e. we get an almost finished HTML string)
  $comment = Markdown($comment);
  // Firefox liked to throw in some &nbsp;'s, but I believe this particular fix is redundant now
  $comment = str_replace("&nbsp;", ' ', $comment);
  // Markdown messes up { and }, replace the ASCII codes by the actual characters for MathJax to pick it up
  $comment = str_replace("&#123;", "\{", $comment);
  $comment = str_replace("&#125;", "\}", $comment);

  // fix macros
  $macros = getMacros();
  $comment = preg_replace(array_keys($macros), array_values($macros), $comment);

  return $comment;
}

function parseReferences($string) {
  // look for \ref before MathJax can and see if they point to existing tags
  $references = array();

  preg_match_all('/\\\ref{[\w-]*}/', $string, $references);
  foreach ($references[0] as $reference) {
    // get the label or tag we're referring to, nothing more
    $target = substr($reference, 5, -1);

    // we're referring to a tag
    if (isValidTag($target)) {
      // regardless of whether the tag exists we insert the link, the user is responsible for meaningful content
      $string = str_replace($reference, '[`' . $target . '`](' . href('tag/' . $target) . ')', $string);
    }
    // the user might be referring to a label
    else {
      // might it be that he is referring to a "local" label, i.e. in the same chapter as the tag?
      // TODO: Is it worth it to do this?
      if (!labelExists($target)) {
        $tag = getTag(strtoupper($_GET['tag']));
        // let's try it with the current chapter in front of the label
        $target = $tag["file"] . '-' . $target;
      }

      // the label (potentially modified) exists in the database (and it is active), so the user is probably referring to it
      // if he declared a \label{} in his string with this particular label value he's out of luck
      if (labelExists($target)) {
        $tag = getTagWithLabel($target);
        $string = str_replace($reference, '[`' . $tag . '`](' . href('tag/' . $tag) . ')', $string);
      }
    }
  }

  return $string;
}

function getCommentsForTag($tag) {
  global $database;

  $comments = array();

  $sql = $database->prepare("SELECT id, tag, author, date, comment, site FROM comments WHERE tag = :tag ORDER BY date");
  $sql->bindParam(':tag', $tag);

  if ($sql->execute()) {
    while ($row = $sql->fetch())
      array_push($comments, $row);
  }

  return $comments;
}

function printIsAreComments($count) {
  if ($count == 1)
    return "is also 1 comment";
  else
    return "are also " . $count . " comments";
}

function printEnclosingComments($tag, $position, $type) {
  $output = "";

  // handle enclosing chapter
  if (in_array($type, array("section", "theorem", "lemma", "definition", "equation", "example", "remark", "proposition", "item", "exercise", "situation", "subsection", "remarks"))) {
    $chapter = getEnclosingChapter($position);
    $chapterComments = getCommentsForTag($chapter["tag"]);

    if (sizeof($chapterComments) > 0)
      $output .= "<p>There " . printIsAreComments(sizeof($chapterComments)) . " on <a href='" . href("tag/" . $chapter["tag"]) . "'>Chapter " . $chapter["book_id"] . ": " . parseAccents($chapter["name"]) . "</a>.";
  }

  // handle enclosing section
  if (in_array($type, array("theorem", "lemma", "definition", "equation", "example", "remark", "proposition", "item", "exercise", "situation", "subsection", "remarks"))) {
    $section = getEnclosingSection($position);
    $sectionComments = getCommentsForTag($section["tag"]);

    if (sizeof($sectionComments) > 0)
      $output .= "<p>There " . printIsAreComments(sizeof($sectionComments)) . " on <a href='" . href("tag/" . $section["tag"]) . "'>Section " . $section["book_id"] . ": " . parseAccents($chapter["name"]) . "</a>.";
  }

  // handle enclosing tag
  if (in_array($type, array("item", "equation"))) {
    $enclosingTag = getEnclosingTag($position);
    $enclosingTagComments = getCommentsForTag($enclosingTag["tag"]);

    if (sizeof($enclosingTagComments) > 0)
      $output .= "<p>There " . printIsAreComments(sizeof($enclosingTagComments)) . " on <a href='" . href("tag/" . $enclosingTag["tag"]) . "'> " . ucfirst($enclosingTag["type"]) . "&nbsp;" . $enclosingTag["book_id"] . "</a>.";
  }

  return $output;
}

function stripChapter($id) {
  $pieces = explode(".", $id);
  return implode(array_splice($pieces, 1), ".");
}

class TagViewPage extends Page {
  private $siblingTags;
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;

    $sql = $this->db->prepare("SELECT tag, name, position, reference, type, book_id, chapter_page, book_page, label, file, value, begin, end, slogan, history FROM tags WHERE tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->tag = $sql->fetch();

    // phantom is actually a chapter
    if (isPhantom($this->tag["label"]))
      $this->tag["type"] = "chapter";
  }

  public function getHead() {
    global $config;
    $value = "";

    $value .= "<script type='text/javascript' src='" . $config["jQuery"] . "'></script>";
    $value .= "<script type='text/javascript' src='" . href("js/tag.js") . "'></script>";
    $value .= "<link rel='stylesheet' type='text/css' href='" . href("css/tag.css") . "'>";

    if ($this->tag["type"] == "chapter") {
      $value .= "<script type='text/javascript' src='" . $config["jQuery"] . "'></script>";
      $value .= "<script type='text/javascript' src='" . href('js/jquery-treeview/jquery.treeview.js') . "'></script>";
      $value .= "<link rel='stylesheet' href='" . href('js/jquery-treeview/jquery.treeview.css') . "' />";
    }
    else {
      $value .= "<script type='text/javascript' src='" . href("js/sfm.js") . "'></script>";
      $value .= "<script type='text/javascript' src='" . href("js/EpicEditor/epiceditor/js/epiceditor.js") . "'></script>";
      $value .= "<script type='text/javascript'>";
      $value .= "  var options = {";
      $value .= "    basePath: '" . href("js/EpicEditor/epiceditor") . "',";
      $value .= "    file: {";
      $value .= "      name: '" . $this->tag["tag"] . "',";
      $value .= "      defaultContent: 'You can type your comment here, use the preview option to see what it will look like.',";
      $value .= "    },";
      $value .= "    theme: {";
      $value .= "      editor: '/themes/editor/stacks-editor.css',";
      $value .= "      preview: '/themes/preview/stacks-preview.css',";
      $value .= "    },";
      $value .= "    parser : sfm,";
      $value .= "    shortcut : {";
      $value .= "      modifier : 0,";
      $value .= "    } ";
      $value .= "  }";
      $value .= "</script>";

      $value .= printMathJax();
    }

    return $value;
  }

  public function getMain() {
    $value = "";
    $value .= "<h2>Tag <var class='tag'>" . $this->tag["tag"] . "</var></h2>";

    if ($this->tag["type"] != "section" and $this->tag["type"] != "chapter")
      $value .= printBreadcrumb($this->tag);

    if ($this->tag["slogan"] != "")
      $value .= "<p id='slogan'><strong>" . parseAccents($this->tag["slogan"]) . "</strong>"; // maybe there will be more advanced LaTeX in slogans at some point?

    $value .= $this->printView();

    if (!empty($this->tag["reference"])) {
      $value .= "<h2 id='references-header'>References</h2>";
      $value .= "<div id='references'>";

      // plaintext view of the reference
      $value .= "<div id='references-text'><p>" . convertLaTeX($this->tag["tag"], $this->tag["file"], $this->tag["reference"]) . "</div>";

      $value .= "</div>";
    }

    if ($this->tag["history"] != "") {
      $value .= "<h2 id='history-header'>Historical remarks</h2>";
      $value .= "<div id='history'>";
      $value .= "<p>" . convertLaTeX($this->tag["tag"], $this->tag["file"], $this->tag["history"]);
      $value .= "</div>";
    }

    $comments = $this->getComments();
    $value .= "<h2 id='comments-header'>Comments (" . count($comments) . ")</h2>";
    $value .= "<div id='comments'>";
    if (count($comments) == 0) {
      $value .= "<p>There are no comments yet for this tag.</p>";
    }
    else {
      foreach($comments as $comment)
        $value .= $this->printComment($comment);
    }
    $value .= printEnclosingComments($this->tag["tag"], $this->tag["position"], $this->tag["type"]);
    $value .= "</div>";

    $value .= "<h2 id='comment-input-header'>Add a comment on tag <var class='tag'>" . $this->tag["tag"] . "</var></h2>";
    $value .= $this->printCommentInput();

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= "<h2>Navigating results</h2>";
    $value .= $this->printNavigation();

    $value .= "<h2>Your location</h2>";
    $value .= $this->printLocation();

    $value .= "<h2 id='citation-header' class='more'>How can you cite this tag?</h2>";
    $value .= $this->printCitation();

    $value .= "<h2>Extras</h2>";
    $value .= "<ul id='extras'>";
    $value .= "<li><a href='" . href("tag/" . $this->tag["tag"] . "/statistics") . "'>statistics</a></li>";

    // only print this when it makes sense
    if (!in_array($this->tag["type"], array("item", "equation", "section", "subsection", "chapter")))
      $value .= "<li><a href='" . href("tag/" . $this->tag["tag"] . "/history") . "'>history</a></li>";

    if (in_array($this->tag["type"], array("section", "subsection", "chapter"))) {
      $value .= "<li id='dependency-graphs'><p>dependency graphs:<p>The dependency graphs of a " . $this->tag["type"] . " are not interesting, to see the dependency graphs of a tag in this section click on the identifier of a lemma, definition, etc.";
    }
    else {
      $value .= "<li id='dependency-graphs'>dependency graphs:<br><br>";
      $value .= printGraphLink($this->tag["tag"], "cluster", "cluster") . "<br>";
      $value .= printGraphLink($this->tag["tag"], "force", "force-directed") . "<br>";
      $value .= printGraphLink($this->tag["tag"], "collapsible", "collapsible") . "<br>";
      $value .= "</li>";
    }
    $value .= "</ul>";

    return $value;
  }
  public function getTitle() {
    if (!empty($this->tag["name"]))
      return " &mdash; Tag " . $this->tag["tag"] . ": " . parseAccents($this->tag["name"]);
    else
      return " &mdash; Tag " . $this->tag["tag"];
  }

  // private functions
  private function getComments() {
    $comments = array();

    $sql = $this->db->prepare("SELECT id, tag, author, date, comment, site FROM comments WHERE tag = :tag ORDER BY date");
    $sql->bindParam(':tag', $this->tag["tag"]);

    if ($sql->execute()) {
      while ($row = $sql->fetch())
        array_push($comments, $row);
    }

    return $comments;
  }

  private function getCitations() {
    $comments = array();

    $sql = $this->db->prepare("SELECT name, text FROM citations WHERE tag = :tag");
    $sql->bindParam(':tag', $this->tag["tag"]);

    if ($sql->execute()) {
      while ($row = $sql->fetch())
        array_push($comments, $row);
    }

    return $comments;
  }

  private function getSiblingTags() {
    // check whether result is already cached
    if ($this->siblingTags == null)
      $this->siblingTags = getSiblingTags($this->tag["position"]);

    return $this->siblingTags;
  }

  private function printCitation() {
    $value = "";

    $value .= "<p>Use:";
    $value .= "<pre style='margin: -.2em 0 .8em 0'><code style='font-size: 90%'>\\cite[Tag " . $this->tag["tag"] . "]{stacks-project}</code></pre>";
    $value .= "<div id='citation-text-more'>";
    $value .= "or one of the following (click to see and copy the code)";
    $value .= "<ul id='citation-options'>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\cite[\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{Tag " . $this->tag["tag"] . "}]{stacks-project}\")'>[Tag " . $this->tag["tag"] . ", Stacks]</a>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\cite[\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . "}]{stacks-project}\")'>[" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . ", Stacks]</a>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{Tag " . $this->tag["tag"] . "}\")'>Tag " . $this->tag["tag"] . "</a>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . "}\")'>" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . "</a>";
    $value .= "</ul>";
    $value .= "<p>For more information, see <a href='" . href("tags") . "'>How to reference tags</a>.</p>";
    $value .= "</div>";

    return $value;
  }
  private function printComment($comment) {
    $value = "";
    $value .= "<div class='comment' id='comment-" . $comment["id"] . "'>";
    $value .= "<a href='#comment-" . $comment["id"] . "'>Comment #" . $comment["id"] . "</a> ";
    $value .= "by <cite class='comment-author'>" . htmlspecialchars($comment["author"]) . "</cite> ";
    if (!empty($comment["site"]))
      $value .=  "(<a href='" . htmlspecialchars($comment['site']) . "'>site</a>) ";
    $date = date_create($comment['date'], timezone_open('GMT'));
    $value .= "on " . date_format($date, "F j, Y \a\t g:i a e") . "\n";
    $value .= "<blockquote>" . parseComment($comment["comment"]) . "</blockquote>";
    $value .= "</div>";

    return $value;
  }

  private function printCommentInput() {
    $value = "";
    $value .= "<div id='comment-input'>";
    $value .= "<p>Your email address will not be published. Required fields are marked.</p>";
    $value .= "<p>In your comment you can use <a href='" . href("markdown") . "'>Markdown</a> and LaTeX style mathematics (enclose it like <code>$\pi$</code>). A preview option is available if you wish to see how it works out (just click on the eye in the lower-right corner).</p>";
    $value .= "<noscript>Unfortunately JavaScript is disabled in your browser, so the comment preview function will not work.</noscript>";
    $value .= "<p>All contributions are licensed under the <a href='https://github.com/stacks/stacks-project/blob/master/COPYING'>GNU Free Documentation License</a>.</p>";

    $value .= "<form name='comment' id='comment-form' action='" . href("php/post.php") . "' method='post'>";
    $value .= "<label for='name'>Name<sup>*</sup>:</label>";
    $value .= "<input type='text' name='name' id='name' class='stored'><br>";
    $value .= "<label for='mail'>E-mail<sup>*</sup>:</label>";
    $value .= "<input type='text' name='mail' id='mail' class='stored'><br>";
    $value .= "<label for='site'>Site:</label>";
    $value .= "<input type='text' name='site' id='site' class='stored'><br>";
    $value .= "<label>Comment:</label> <span id='epiceditor-status'></span>";
    $value .= "<textarea name='comment' id='comment-textarea' cols='80' rows='10'></textarea>";
    $value .= "<div id='epiceditor'></div>";
    $value .= "<script type='text/javascript' src='" . href("js/editor.js") . "'></script>";

    $value .= "<p>In order to prevent bots from posting comments, we would like you to prove that you are human. You can do this by <em>filling in the name of the current tag</em> in the following box. So in case this where tag&nbsp;<var class='tag'>0321</var> you just have to write&nbsp;<var class='tag'>0321</var>. Beware of the difference between the letter&nbsp;'<var class='tag'>O</var>' and the digit&nbsp;<var class='tag'>0</var>.";
    $value .= "<p>This <abbr title='Completely Automated Public Turing test to tell Computers and Humans Apart'>captcha</abbr> seems more appropriate than the usual illegible gibberish, right?</p>";
    $value .= "<label for='check'>Tag:</label>";
    $value .= "<input type='text' name='check' id='check'><br>";
    $value .= "<input type='hidden' name='tag' value='" . $this->tag["tag"] . "'>";
    $value .= "<input type='submit' id='comment-submit' value='Post comment'>";
    $value .= "</form>";
    $value .= "</div>";

    return $value;
  }

  private function printLocation() {
    $value = "";

    $value .= "<p>You're at</p>";

    $value .= "<ul>";
    // TODO idea: display a dialog box asking the user whether he really wants to open the pdf (especially book.pdf) as it will take a while?
    switch ($this->tag["type"]) {
      // items have book_id equal to their enumeration number, so look up tag etc from position
      case "item":
        $containingTag = getEnclosingTag($this->tag["position"]);
        $chapter = getChapter(getChapterFromID($containingTag["book_id"]));
        $value .= "<li>Item&nbsp;" . $this->tag["book_id"] . " of the enumeration in <a href='" . href("tag/" . $containingTag["tag"]) . "'>" . ucfirst($containingTag["type"]) . "&nbsp;" . stripChapter($containingTag["book_id"]) . "</a> on <a href='" . href("download/" . $chapter["filename"] . ".pdf#nameddest=" . $containingTag["tag"]) . "'>page&nbsp;" . $this->tag["chapter_page"] . "</a> of <a href='" . href("chapter/" . $chapter["number"]) . "'>Chapter&nbsp;" . $chapter["number"] . ": " . parseAccents($chapter["title"]) . "</a>";

        break;

      case "chapter":
        $chapter = getChapter(getChapterFromID($this->tag["book_id"]));
        $value .= "<li><a href='" . href("download/" . $chapter["filename"] . ".pdf") . "'>Chapter&nbsp;" . $this->tag["book_id"] . "</a> on <a href='" . href("download/book.pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["book_page"] . "</a> of the book";
        break;

      case "equation":
        $containingTag = getEnclosingTag($this->tag["position"]);
        $chapter = getChapter(getChapterFromID($containingTag["book_id"]));
        $value .= "<li>Equation&nbsp;" . stripChapter($this->tag["book_id"]) . " in <a href='" . href("tag/" . $containingTag["tag"]) . "'>" . ucfirst($containingTag["type"]) . "&nbsp;" . stripChapter($containingTag["book_id"]) . "</a> on <a href='" . href("download/" . $chapter["filename"] . ".pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["chapter_page"] . "</a> of <a href='" . href("chapter/" . $chapter["number"]) . "'>Chapter&nbsp;" . $chapter["number"] . ": " . parseAccents($chapter["title"]) . "</a>";
        $value .= "<li>Equation&nbsp;" . $this->tag["book_id"] . " on <a href='" . href("download/book.pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["book_page"] . "</a> of the book";
        break;

      default:
        $chapter = getChapter(getChapterFromID($this->tag["book_id"]));
        $value .= "<li>" . ucfirst($this->tag["type"]) . "&nbsp;" . stripChapter($this->tag["book_id"]) . " on <a href='" . href("download/" . $chapter["filename"] . ".pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["chapter_page"] . "</a> of <a href='" . href("chapter/" . $chapter["number"]) . "'>Chapter&nbsp;" . $chapter["number"] . ": " . parseAccents($chapter["title"]) . "</a>";
        $value .= "<li>" . ucfirst($this->tag["type"]) . "&nbsp;" . $this->tag["book_id"] . " on <a href='" . href("download/book.pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["book_page"] . "</a> of the book";
        break;
    }

    if ($this->tag["type"] != "chapter")
      $value .= "<li><a href='https://github.com/stacks/stacks-project/blob/master/" . $chapter["filename"] . ".tex#L" . $this->tag["begin"] . "-" . $this->tag["end"] . "'>lines " . $this->tag["begin"] . "&ndash;" . $this->tag["end"] . "</a> of <a href='https://github.com/stacks/stacks-project/blob/master/" . $chapter["filename"] . ".tex'><var>" . $chapter["filename"] . ".tex</var></a>";
    else
      $value .= "<li>which corresponds to the file <a href='https://github.com/stacks/stacks-project/blob/master/" . $chapter["filename"] . ".tex'><var>" . $chapter["filename"] . ".tex</var></a>";

    $value .= "</ul>";

    return $value;
  }

  private function printNavigation() {
    $value = "";

    switch ($this->tag["type"]) {
      case "section": // TODO what about subsection?
        $value .= "<p class='navigation'>";
        // previous section
        $sql = $this->db->prepare('SELECT sections.number, sections.title, tags.tag FROM sections, tags WHERE tags.position < :position AND tags.type = "section" AND sections.number LIKE "%.%" AND tags.book_id = sections.number ORDER BY tags.position DESC LIMIT 1');
        $sql->bindParam(':position', $this->tag["position"]);

        if ($sql->execute()) {
          // at most one will be selected
          while ($row = $sql->fetch()) {
            $value .= "<span class='left'><a title='" . $row["number"] . " " . parseAccents($row["title"]) . "' href='" . href("tag/" . $row["tag"]) . "'>&lt;&lt; Previous section</a></span>";
          }
        }
        // next section
        $sql = $this->db->prepare('SELECT sections.number, sections.title, tags.tag FROM sections, tags WHERE tags.position > :position AND tags.type = "section" AND tags.book_id = sections.number AND sections.number LIKE "%.%" ORDER BY tags.position LIMIT 1');
        $sql->bindParam(':position', $this->tag["position"]);

        if ($sql->execute()) {
          while ($row = $sql->fetch()) {
            $value .= "<span class='right'><a title='" . $row["number"] . " " . parseAccents($row["title"]) . "' href='" . href("tag/" . $row["tag"]) . "'>Next section &gt;&gt;</a></span>";
          }
        }
        $value .= "</p>";
        break;

      case "lemma": // TODO and some others
        // print enclosing section here? YES!
        break;
    }

    $siblingTags = $this->getSiblingTags();
    if (!empty($siblingTags)) {
      $value .= "<p class='navigation'>";
      if (isset($siblingTags["previous"]))
        $value .= "<span class='left'><a title='" . $siblingTags["previous"]["tag"] . " " . $siblingTags["previous"]["label"] . "' href='" . href("tag/" . $siblingTags["previous"]["tag"]) . "'>&lt;&lt; Previous tag</a></span>";
      if (isset($siblingTags["next"]))
        $value .= "<span class='right'><a title='" . $siblingTags["next"]["tag"] . " " . $siblingTags["next"]["label"] . "' href='" . href("tag/" . $siblingTags["next"]["tag"]) . "'>Next tag &gt;&gt;</a></span>";
      $value .= "<br style='clear:both'></p>";
    }

    return $value;
  }

  private function printView() {
    $value = "";
    if ($this->tag["type"] == "chapter") {
      $part = getPart($this->tag["book_id"]);

      $value .= "<h3>Chapter " . $this->tag["book_id"] . ": " . $this->tag["name"] . "</h3>";
      $value .= "<p>This tag corresponds to Chapter " . $this->tag["book_id"] . ": " . parseAccents($this->tag["name"]) . " of <a href='" . href("browse#" . partToIdentifier($part)) . "'>" . parseAccents($part) . "</a>, and contains no further text. To view the contents of the first section in this chapter, go to the next tag.</p>";
      $value .= "<p>This chapter contains the following tags</p>";
      $value .= "<div id='control'>";
      $value .= "<p><a href='#'><img src='" . href("js/jquery-treeview/images/minus.gif") . "'> Collapse all</a>";
      $value .= " ";
      $value .= "<a href='#'><img src='" . href("js/jquery-treeview/images/plus.gif") . "'> Expand all</a>";
      $value .= "</div>";
      $value .= "<div id='treeview'>";
      $value .= "<a href='" . href("tag/" . $this->tag["tag"]) . "'>Tag <var class='tag'>" . $this->tag["tag"] . "</var></a> points to Chapter " . $this->tag["book_id"] . ": " . parseAccents($this->tag["name"]);
      $value .= printToC($this->tag["book_id"]);
      $value .= "</div>";
      $value .= "<script type='text/javascript' src='" . href("js/chapter.js") . "'></script>";
    }
    else {
      // only display for non-chapters
      $value .= "<p id='code-link' class='toggle'><a href='#code'>code</a></p>";

      $value .= "<blockquote class='rendered'>";
      $value .= convertLaTeX($this->tag["tag"], $this->tag["file"], $this->tag["value"]);

      // handle footnotes
      global $footnotes;
      $value .= "<div class='footnotes'>";
      $value .= "<ol>";
      foreach ($footnotes as $i => $footnote) {
        $value .= "<li class='footnote' id='fn:" . $i . "'>" . $footnote . "<a href='#fnref:" . $i . "' title='return to main text'> &uarr;</a>";
      }
      $value .= "</ol>";
      $value .= "</div>";

      $value .= "</blockquote>";
    }

    // only display for non-chapters
    if ($this->tag["type"] != "chapter") {
      $value .= "<p id='rendered-link' class='toggle'><a href='#rendered'>view</a></p>";

      $value .= "<div id='code'>";

      $value .= "<p>The code snippet corresponding to this tag is a part of the file <a href='https://github.com/stacks/stacks-project/blob/master/" . $this->tag["file"] . ".tex'><var>" . $this->tag["file"] . ".tex</var></a> and is located in <a href='https://github.com/stacks/stacks-project/blob/master/" . $this->tag["file"] . ".tex#L" . $this->tag["begin"] . "-" . $this->tag["end"] . "'>lines " . $this->tag["begin"] . "&ndash;" . $this->tag["end"] . "</a> (see <a href='" . href("tags#stacks-epoch") . "'>updates</a> for more information).";
      $value .= "<pre><code>";

      $code = preprocessCode($this->tag["value"]);

      // link labels to the corresponding tag
      $count = preg_match_all('/\\\label{([\w-\*]+)}/', $code, $references);
      for ($i = 0; $i < $count; ++$i)
        $code = str_replace($references[0][$i], "\\label{<a href='" . href("tag/" . getTagWithLabel($this->tag["file"] . "-" . $references[1][$i])) . "'>" . $references[1][$i] . "</a>}", $code);

      $value .= $code;
      $value .= "</code></pre>";
      $value .= "</div>";
    }

    $value .= $this->printNavigation();

    return $value;
  }

}

?>
