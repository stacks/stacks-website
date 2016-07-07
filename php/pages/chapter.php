<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/sections.php");

function printToCitem($tag) {
  $value = "";

  $value .= "<li><a title='" . $tag["label"] . "' href='" . href("tag/" . $tag["tag"]) . "'>Tag <var>" . $tag["tag"] . "</var></a> points to " . ucfirst($tag["type"]) . " " . $tag["book_id"];
  // in these cases we can print a name
  if (($tag["type"] == "section" or $tag["type"] == "subsection") or (!in_array($tag["type"], array("item", "equation")) and !empty($tag["name"])))
    $value .= ": " . parseAccents($tag["name"]);

  return $value;
}

function printToC($chapter) {
  $value = "";
  $tags = array();
  global $database;

  $sql = $database->prepare("SELECT tag, label, book_id, type, book_page, file, name FROM tags WHERE active = 'TRUE' AND book_id LIKE '" . $chapter . ".%' ORDER BY position");

  if ($sql->execute())
    $tags = $sql->fetchAll();

  // start global list
  $value .= "<ul>";

  // keep track of how many <ul>'s where issued
  $depth = 0;
  // are we printing a list of equations?
  $equationMode = false;
  // have we just issued a new sub(section)?
  $sectionIssued = false;

  foreach ($tags as $tag) {
    // we have finished a list of equations not directly contained in a (sub)section
    if ($tag["type"] != "equation" and !$sectionIssued and $equationMode) {
      $equationMode = false;
      $value .= "</ul>";
      $depth--;
    }

    // just issued a section, if an equation occurs immediately after this do not start a new list
    if ($tag["type"] == "section" or $tag["type"] == "subsection")
      $sectionIssued = true;

    switch ($tag["type"]) {
      case "section":
        // do not close the container <ul>
        $value .= str_repeat("</ul>", max($depth - 1, 0));
        $depth = 2;
        $value .= printToCitem($tag);
        $value .= "<ul>";
        break;

      case "subsection":
        // subsections mustn't close the section, therefore -2
        $value .= str_repeat("</ul>", $depth - 2);
        $depth = 3;
        $value .= printToCitem($tag);
        $value .= "<ul>";
        break;

      case "equation":
        // start new list because the equations belong to something like a Lemma
        if (!$sectionIssued and !$equationMode) {
          $value .= "<ul>";
          $depth++;
        }
        $value .= printToCitem($tag);
        break;

      default:
        $sectionIssued = false;
        $value .= printToCitem($tag);
        break;
    }

    // let it be known whether we just issued an equation or not
    $equationMode = ($tag["type"] == "equation");
  }

  // end all pending lists,
  $value .= str_repeat("</ul>", $depth);

  return $value;
}

class ChapterPage extends Page {
  private $chapter;

  public function __construct($database, $chapter) {
    assert(sectionExists($chapter));

    $this->db = $database;

    $sql = $this->db->prepare("SELECT sections.title, sections.filename, sections.number, tags.tag FROM sections, tags WHERE sections.number = :number AND sections.number = tags.book_id AND type = 'section'");
    $sql->bindParam(":number", $chapter);

    if ($sql->execute())
      $this->chapter = $sql->fetch();
  }

  public function getHead() {
    $value = "";
    global $config;

    $value .= "<script type='text/javascript' src='" . $config["jQuery"] . "'></script>";
    $value .= "<script type='text/javascript' src='" . href('js/jquery-treeview/jquery.treeview.js') . "'></script>";
    $value .= "<link rel='stylesheet' href='" . href('js/jquery-treeview/jquery.treeview.css') . "' />";

    return $value;
  }

  public function getMain() {
    $value = "";

    $value .= "<h2>Tree view for Chapter " . $this->chapter["number"] . ": " . parseAccents($this->chapter["title"]) . "</h2>";
    $value .= $this->printNavigation();

    $value .= "<div id='control'>";
    $value .= "<p><a href='#'><img src='" . href("js/jquery-treeview/images/minus.gif") . "'> Collapse all</a>";
    $value .= " ";
    $value .= "<a href='#'><img src='" . href("js/jquery-treeview/images/plus.gif") . "'> Expand all</a></p>";
    $value .= "</div>";

    $value .= "<div id='treeview'>";
    $value .= "<a href='" . href("tag/" . $this->chapter["tag"]) . "'>Tag " . $this->chapter["tag"] . "</a> points to Chapter " . $this->chapter["number"] . ": " . parseAccents($this->chapter["title"]);
    $value .= printToC($this->chapter["number"]);
    $value .= "</div>";

    $value .= "<script type='text/javascript' src='" . href("js/chapter.js") . "'></script>";

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= "<h2>Navigating chapters</h2>";
    $value .= $this->printNavigation(false);

    $value .= "<h2>Downloads</h2>";
    $value .= "<ul>";
    $value .= "<li><a href='" . href("download/" . $this->chapter["filename"] . ".pdf") . "'><code>pdf</code> of this chapter</a>";
    $value .= "<li><a href='https://github.com/stacks/stacks-project/blob/master/" . $this->chapter["filename"] . ".tex'><code>tex</code> file for this chapter</a>";
    $value .= "</ul>";

    $value .= "<h2>Permalink</h2>";
    $value .= "<p>The number of chapters in the Stacks project is likely to change. So this URL is <strong>not stable</strong>.</p>";
    $value .= "<p>To provide a truly <em>stable link</em>, use the <a href='" . href("tag/" . $this->chapter["tag"]) . "'>corresponding tag lookup page for tag " . $this->chapter["tag"] . "</a>. This identifier will never change.</p>";
    $value .= "<p>When referring to this chapter of the Stacks project, it is better to say<blockquote>Tag " . $this->chapter["tag"] . "<br>Chapter " . $this->chapter["tag"] . "</blockquote> or refer to the full name of the chapter.</p>";

    $value .= "<h2>Statistics</h2>";
    $value .= "<ul>";
    $value .= "<li>" . getLineCount($this->db, $this->chapter["filename"] . ".tex") . " lines of code</li>";
    $value .= "<li>" . getTagsInFileCount($this->db, $this->chapter["filename"]) . " tags</li>";
    $value .= "<li>" . (getSectionsInFileCount($this->db, $this->chapter["filename"]) - 1) . " sections</li>"; // -1 to take care of the phantom section
    $value .= "<li>" . getPageCount($this->db, $this->chapter["filename"]) . " pages</li>";
    $value .= "</ul>";

    return $value;
  }
  public function getTitle() {
    return " &mdash; Chapter " . $this->chapter["number"] . ": " . parseAccents($this->chapter["title"]);
  }


  private function printNavigation($displayTitle = true) {
    $value = "";

    $value .= "<p class='navigation'>";
    // back
    if (sectionExists(intval($this->chapter["number"]) - 1)) {
      $previousChapter = getChapter(intval($this->chapter["number"]) - 1);
      if ($displayTitle) {
        $value .= "<span class='left'><a href='" . href("chapter/" . (intval($this->chapter["number"]) - 1)) . "'>";
        $value .= "&lt;&lt; Chapter " . (intval($this->chapter["number"]) - 1) . ": " . parseAccents($previousChapter["title"]);
      }
      else {
        $value .= "<span class='left'><a href='" . href("chapter/" . (intval($this->chapter["number"]) - 1)) . "' title='Chapter " . $previousChapter["number"] . ": " . parseAccents($previousChapter["title"]) . "'>";
        $value .= "&lt;&lt; Previous chapter";
      }
      $value .= "</a></span>";
    }
    // forward
    if (sectionExists(intval($this->chapter["number"]) + 1)) {
      $nextChapter = getChapter(intval($this->chapter["number"]) + 1);
      if ($displayTitle) {
        $value .= "<span class='right'><a href='" . href("chapter/" . (intval($this->chapter["number"]) + 1)) . "'>";
        $value .= "Chapter " . (intval($this->chapter["number"]) + 1) . ": " . parseAccents($nextChapter["title"]) . " &gt;&gt;";
      }
      else {
        $value .= "<span class='right'><a href='" . href("chapter/" . (intval($this->chapter["number"]) + 1)) . "' title='Chapter " . $nextChapter["number"] . ": " . parseAccents($nextChapter["title"]) . "'>";
        $value .= "Next chapter &gt;&gt;";
      }
      $value .= "</a></span>";
    }
    $value .= "</p>";

    return $value;
  }


}

?>

