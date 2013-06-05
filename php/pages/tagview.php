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

function getChapter($id) {
  $parts = explode(".", $id);
  return $parts[0];
}

function isPhantom($label) {
  return substr_compare($label, "section-phantom", -15, 15) == 0;
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

  // fix underscores (all underscores in math mode will be escaped
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
  // Google Chrome somehow adds this character so let's remove it
  $comment = str_replace("\xA0", ' ', $comment);
  // Firefox liked to throw in some &nbsp;'s, but I believe this particular fix is redundant now
  $comment = str_replace("&nbsp;", ' ', $comment);

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

function preprocessCode($code) {
  // remove irrelevant new lines at the end
  $code = trim($code);
  // escape stuff
  $code = htmlentities($code);

  // but links should work: tag links are made up from alphanumeric characters, slashes, dashes and underscores, while the LaTeX label contains only alphanumeric characters and dashes
  $code = preg_replace('/&lt;a href=&quot;\/([A-Za-z0-9\/\-]+)&quot;&gt;([A-Za-z0-9\-]+)&lt;\/a&gt;/', '<a href="' . href("") . '$1">$2</a>', $code);

  return $code;
}

function stripChapter($id) {
  return implode(array_splice(explode(".", $id), 1), ".");
}

class TagViewPage extends Page {
  private $siblingTags;
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;

    try {
      $sql = $this->db->prepare("SELECT tag, name, position, type, book_id, chapter_page, book_page, label, file, value, begin, end FROM tags WHERE tag = :tag");
      $sql->bindParam(":tag", $tag);

      if ($sql->execute())
        $this->tag = $sql->fetch();
      // else
      // TODO error handling
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

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

    return $value;
  }

  public function getMain() {
    $value = "";
    $value .= "<h2>Tag <var>" . $this->tag["tag"] . "</var></h2>";

    if ($this->tag["type"] != "section" and $this->tag["type"] != "chapter")
      $value .= $this->printBreadcrumb();

    $value .= $this->printView();

    $comments = $this->getComments(); // TODO initialize in constructor?
    $value .= "<h2 id='comments-header'>Comments (" . count($comments) . ")</h2>";
    $value .= "<div id='comments'>";
    if (count($comments) == 0) {
      $value .= "<p>There are no comments yet for this tag.</p>";
      $value .= "<script type='text/javascript'>\$(document).ready(toggleComments());</script>";
    }
    else {
      foreach($comments as $comment)
        $value .= $this->printComment($comment);
    }
    $value .= "</div>";

    $value .= "<h2 id='comment-input-header'>Add a comment on tag <var>" . $this->tag["tag"] . "</var></h2>";
    $value .= $this->printCommentInput();

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= "<h2>Navigating results</h2>";
    $value .= $this->printNavigation();

    $value .= "<h2>Your location</h2>";
    $value .= $this->printLocation();

    $value .= "<h2>How can you cite this tag?</h2>";
    $value .= $this->printCitation();

    $value .= "<h2>Extras</h2>";
    $value .= "<ul>";
    $value .= "<li><a href='#'>dependency graph</a></li>";
    $value .= "<li><a href='" . href("tag/" . $this->tag["tag"] . "/statistics") . "'>statistics</a></li>";
    $value .= "</ul>";

    return $value;
  }
  public function getTitle() {
    if(!empty($this->tag["title"]))
      return " &mdash; Tag " . $this->tag["tag"] . ": " . parseAccents($this->tag["name"]);
    else
      return " &mdash; Tag " . $this->tag["tag"];
  }

  // private functions
  private function getComments() {
    $comments = array();

    try {
      $sql = $this->db->prepare("SELECT id, tag, author, date, comment, site FROM comments WHERE tag = :tag ORDER BY date");
      $sql->bindParam(':tag', $this->tag["tag"]);

      if ($sql->execute()) {
        while ($row = $sql->fetch())
          array_push($comments, $row);
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();

      return array();
    }

    return $comments;
  }

  private function getSiblingTags() {
    // check whether result is already cached (TODO initialize in constructor?)
    if ($this->siblingTags != null)
      return $this->siblingTags;

    if (positionExists($this->tag["position"] - 1))
      $this->siblingTags["previous"] = getTagAtPosition($this->tag["position"] - 1);
    if (positionExists($this->tag["position"] + 1))
      $this->siblingTags["next"] = getTagAtPosition($this->tag["position"] + 1);

    return $this->siblingTags;
  }

  private function printCitation() {
    $value = "";

    $value .= "<p>Use:";
    $value .= "<pre><code>\\cite[Tag " . $this->tag["tag"] . "]{stacks-project}</code></pre>";
    $value .= "or one of the following (click to see and copy the code)";
    $value .= "<ul id='citation-options'>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\cite[\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{Tag " . $this->tag["tag"] . "}]{stacks-project}\")'>[Tag " . $this->tag["tag"] . ", Stacks]</a>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\cite[\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . "}]{stacks-project}\")'>[" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . ", Stacks]</a>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{Tag " . $this->tag["tag"] . "}\")'>Tag " . $this->tag["tag"] . "</a>";
    $value .= "<li><a href='javascript:copyToClipboard(\"\\\\href{http://stacks.math.columbia.edu/tag/" . $this->tag["tag"] . "}{" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . "}\")'>" . ucfirst($this->tag["type"]) . " " . $this->tag["tag"] . "</a>";
    $value .= "</ul>";
    $value .= "<p>For more information, see <a href='#'>How to reference tags</a>.</p>";

    return $value;
  }
  private function printComment($comment) {
    $value = "";
    $value .= "<div class='comment' id='comment-" . $comment["id"] . "'>";
    $value .= "<a href='#comment-" . $comment["id"] . "'>Comment #" . $comment["id"] . "</a> ";
    $value .= "by <cite class='comment-author'>" . htmlspecialchars($comment["author"]) . "</cite> ";
    if (!empty($comment["site"])) 
      $value .=  "<a href='" . htmlspecialchars($comment['site']) . "'>site</a>)";
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
    $value .= "<p>In your comment you can use <a href='#'>Markdown</a> and LaTeX style mathematics (enclose it like <code>$\pi$</code>). A preview option is available if you wish to see how it works out (just click on the eye in the lower-right corner).</p>"; // TODO fix link
    $value .= "<noscript>Unfortunately JavaScript is disabled in your browser, so the comment preview function will not work.</noscript>";

    $value .= "<form name='comment' id='comment-form' action='#' method='post'>";
    $value .= "<label for='name'>Name<sup>*</sup>:</label>";
    $value .= "<input type='text' name='name' id='name'><br>";
    $value .= "<label for='mail'>E-mail<sup>*</sup>:</label>";
    $value .= "<input type='text' name='email' id='mail'><br>";
    $value .= "<label for='site'>Site:</label>";
    $value .= "<input type='text' name='site' id='site'><br>";
    $value .= "<label>Comment:</label> <span id='epiceditor-status'></span>";
    $value .= "<textarea name='comment' id='comment-textarea' cols='80' rows='10'></textarea>";
    $value .= "<div id='epiceditor'></div>";
    $value .= "<script type='text/javascript' src='" . href("js/editor.js") . "'></script>";

    $value .= "<p>In order to prevent bots from posting comments, we would like you to prove that you are human. You can do this by <em>filling in the name of the current tag</em> in the following box. So in case this is tag&nbsp;<var>0321</var> you just have to write&nbsp;<var>0321</var>. This <abbr title='Completely Automated Public Turing test to tell Computers and Humans Apart'>captcha</abbr> seems more appropriate than the usual illegible gibberish, right?</p>";
    $value .= "<label for='check'>Tag:</label>";
    $value .= "<input type='text' name='check' id='check'><br>";
    $value .= "<input type='hidden' name='tag' value='03D9'>";
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
        $chapter = get_chapter(getChapter($containingTag["book_id"]));
        $value .= "<li>Item&nbsp;" . $this->tag["book_id"] . " of the enumeration in <a href='" . href("tag/" . $containingTag["tag"]) . "'>" . ucfirst($containingTag["type"]) . "&nbsp;" . stripChapter($containingTag["book_id"]) . "</a> on <a href='" . href("downloads/" . $chapter["filename"] . ".pdf#nameddest=" . $containingTag["tag"]) . "'>page&nbsp;" . $this->tag["chapter_page"] . "</a> of <a href='" . href("chapter/" . $chapter["number"]) . "'>Chapter&nbsp;" . $chapter["number"] . ": " . $chapter["title"] . "</a>";

        break;

      case "phantom":
        $chapter = get_chapter(getChapter($this->tag["book_id"]));
        $value .= "<li>Chapter&nbsp;" . $this->tag["book_id"] . " on <a href='" . href("download/book.pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["book_page"] . "</a> of the book";
        break;

      case "equation":
        $containingTag = getEnclosingTag($this->tag["position"]);
        $chapter = get_chapter(getChapter($containingTag["book_id"]));
        $value .= "<li>Equation&nbsp;" . stripChapter($this->tag["book_id"]) . " in <a href='" . href("tag/" . $containingTag["tag"]) . "'>" . ucfirst($containingTag["type"]) . "&nbsp;" . stripChapter($containingTag["book_id"]) . "</a> on <a href='" . href("downloads/" . $chapter["filename"] . ".pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["chapter_page"] . "</a> of <a href='" . href("chapter/" . $chapter["number"]) . "'>Chapter&nbsp;" . $chapter["number"] . ": " . $chapter["title"] . "</a>";
        $value .= "<li>Equation&nbsp;" . $this->tag["book_id"] . " on <a href='" . href("download/book.pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["book_page"] . "</a> of the book";
        break;

      default:
        $chapter = get_chapter(getChapter($this->tag["book_id"]));
        $value .= "<li>" . ucfirst($this->tag["type"]) . "&nbsp;" . stripChapter($this->tag["book_id"]) . " on <a href='" . href("download/" . $chapter["filename"] . ".pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["chapter_page"] . "</a> of <a href='" . href("chapter/" . $chapter["number"]) . "'>Chapter&nbsp;" . $chapter["number"] . ": " . $chapter["title"] . "</a>";
        $value .= "<li>" . ucfirst($this->tag["type"]) . "&nbsp;" . $this->tag["book_id"] . " on <a href='" . href("download/book.pdf#nameddest=" . $this->tag["tag"]) . "'>page&nbsp;" . $this->tag["book_page"] . "</a> of the book";
        break;
    }
    
    if ($this->tag["type"] != "phantom")
      $value .= "<li><a href='https://github.com/stacks/stacks-project/blob/master/" . $chapter["filename"] . ".tex#L" . $this->tag["begin"] . "-" . $this->tag["end"] . "'>lines " . $this->tag["begin"] . "&ndash;" . $this->tag["end"] . "</a> of <a href='https://github.com/stacks/stacks-project/blob/master/" . $chapter["filename"] . ".tex'><var>" . $chapter["filename"] . ".tex</var></a>";
    else
      $value .= "<li>in <a href='https://github.com/stacks/stacks-project/blob/master/" . $chapter["filename"] . ".tex'><var>" . $chapter["filename"] . ".tex</var></a>";

    $value .= "</ul>";

    return $value;
  }

  private function printNavigation() {
    // TODO cache this?
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
            $value .= "<span class='left'><a title='" . $row["number"] . " " . $row["title"] . "' href='" . href("tag/" . $row["tag"]) . "'>&lt;&lt; Previous section</a></span>";
          }
        }
        // next section
        $sql = $this->db->prepare('SELECT sections.number, sections.title, tags.tag FROM sections, tags WHERE tags.position > :position AND tags.type = "section" AND tags.book_id = sections.number AND sections.number LIKE "%.%" ORDER BY tags.position LIMIT 1');
        $sql->bindParam(':position', $this->tag["position"]);

        if ($sql->execute()) {
          while ($row = $sql->fetch()) {
            $value .= "<span class='right'><a title='" . $row["number"] . " " . $row["title"] . "' href='" . href("tag/" . $row["tag"]) . "'>Next section &gt;&gt;</a></span>";
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
      $value .= "</p>";
    }

    return $value;
  }

  private function printBreadcrumb() {
    $output = "";

    $chapter = getEnclosingChapter($this->tag["position"]);
    $section = getEnclosingSection($this->tag["position"]);
    $output .= "<p><a href='" . href("chapter/" . $chapter["book_id"]) . "'>Chapter " . $chapter["book_id"] . ": " . $chapter["name"] . "</a>&nbsp;&nbsp;&gt;&nbsp;&nbsp;";
    $output .= "<a href='" . href("tag/" . $section["tag"]) . "'>Section " . $section["book_id"] . ": " . $section["name"] . "</a>";

    return $output;
  }

  private function printView() {
    $value = "";
    $value .= "<p id='code-link' class='toggle'><a href='#code'>code</a></p>";
    $value .= "<blockquote id='rendered'>";
    if ($this->tag["type"] == "chapter") {
      $value .= "<h3>Chapter " . $this->tag["book_id"] . ": " . $this->tag["name"] . "</h3>";
      $value .= "<p>This tag corresponds to <a href='" . href("chapter/" . $this->tag["book_id"]) . "'>Chapter " . $this->tag["book_id"] . ": " . parseAccents($this->tag["name"]) . "</a>, and contains no further text. To view the contents of the chapter, go to the next tag.</p>";
    }
    else {
      $value .= convertLaTeX($this->tag["tag"], $this->tag["file"], $this->tag["value"]);
    }
    $value .= "</blockquote>";

    $value .= "<p id='rendered-link' class='toggle'><a href='#rendered'>view</a></p>";
    $value .= "<div id='code'>";
    if ($this->tag["type"] == "chapter") {
      $value .= "<p>The tag corresponds to the file <a href='https://github.com/stacks/stacks-project/blob/master/" . $this->tag["file"] . ".tex'><var>" . $this->tag["file"] . ".tex</var></a>, or equivalently to the whole of <a href='" . href("chapter/" . $this->tag["book_id"]) . "'>Chapter " . $this->tag["book_id"] . ": " . parseAccents($this->tag["name"]) . "</a>. No code preview is provided here.</p>";
    }
    else {
      $value .= "<p>The code snippet corresponding to this tag is a part of the file <a href='https://github.com/stacks/stacks-project/blob/master/" . $this->tag["file"] . ".tex'><var>" . $this->tag["file"] . ".tex</var></a> and is located in <a href='https://github.com/stacks/stacks-project/blob/master/" . $this->tag["file"] . "#L" . $this->tag["begin"] . "-" . $this->tag["end"] . "'>lines " . $this->tag["begin"] . "&ndash;" . $this->tag["end"] . "</a> (see <a href='#'>updates</a> for more information)."; // TODO line references, and a page on the updating process
      $value .= "<pre><code>";
      $value .= preprocessCode($this->tag["value"]);
      $value .= "</code></pre>";
    }
    $value .= "</div>";

    $value .= $this->printNavigation();

    return $value;
  }

}

?>
