<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/sections.php");

class ChapterPage extends Page {
  private $chapter;

  public function __construct($database, $chapter) {
    assert(section_exists($chapter));

    $this->db = $database;
    try {
      $sql = $this->db->prepare('SELECT title, filename, number FROM sections WHERE number = :number');
      $sql->bindParam(':number', $chapter);
  
      if ($sql->execute())
        $this->chapter = $sql->fetch();
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }

  public function getHead() {
    $value = "";

    global $jQuery;
    $value .= "<script type='text/javascript' src='" . $jQuery . "'></script>";
    $value .= "<script type='text/javascript' src='" . href('js/jquery-treeview/jquery.treeview.js') . "'></script>";
    $value .= "<link rel='stylesheet' href='" . href('js/jquery-treeview/jquery.treeview.css') . "' />";

    return $value;
  }

  public function getMain() {
    $value = "";

    $value .= "<h2>Tree view for Chapter " . $this->chapter["number"] . ": " . parseAccents($this->chapter["title"]) . "</h2>";
    $value .= $this->printNavigation();

    $value .= "<div id='control'>";
    $value .= "<a href='#'><img src='" . href("js/jquery-treeview/images/minus.gif") . "'> Collapse all</a>";
    $value .= " ";
    $value .= "<a href='#'><img src='" . href("js/jquery-treeview/images/plus.gif") . "'> Expand all</a>";
    $value .= "</div>";

    $value .= "<div id='treeview'>";
    $value .= $this->printTags();
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

    return $value;
  }
  public function getTitle() {
    return "";
  }


  private function printNavigation($displayTitle = true) {
    $value = "";

    $value .= "<p class='navigation'>";
    // back
    if (section_exists(intval($this->chapter["number"]) - 1)) {
      $previousChapter = get_chapter(intval($this->chapter["number"]) - 1);
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
    if (section_exists(intval($this->chapter["number"]) + 1)) {
      $nextChapter = get_chapter(intval($this->chapter["number"]) + 1);
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

  private function printTag($tag) {
    $value = "";

    $value .= "<li><a title='" . $tag["label"] . "' href='" . href("tag/" . $tag["tag"]) . "'>Tag <var>" . $tag["tag"] . "</var></a> points to " . ucfirst($tag["type"]) . " " . $tag["book_id"];
    // in these cases we can print a name
    if (($tag["type"] == "section" or $tag["type"] == "subsection") or (!in_array($tag["type"], array("item", "equation")) and !empty($tag["name"])))
      $value .= ": " . parseAccents($tag["name"]);

    return $value;
  }

  private function printTags() {
    $value = "";
    $tags = array();

    try {
      $sql = $this->db->prepare("SELECT tag, label, book_id, type, book_page, file, name FROM tags WHERE active = 'TRUE' AND book_id LIKE '" . $this->chapter["number"] . ".%' ORDER BY position");
      
      if ($sql->execute())
        $tags = $sql->fetchAll();
      else
        print $this->db->errorInfo();
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

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
          $value .= $this->printTag($tag);
          $value .= "<ul>";
          break;

        case "subsection":
          // subsections mustn't close the section, therefore -2
          $value .= str_repeat("</ul>", $depth - 2);
          $depth = 3;
          $value .= $this->printTag($tag);
          $value .= "<ul>";
          break;

        case "equation":
          // start new list because the equations belong to something like a Lemma
          if (!$sectionIssued and !$equationMode) {
            $value .= "<ul>";
            $depth++;
          }
          $value .= $this->printTag($tag);
          break;

        default:
          $sectionIssued = false;
          $value .= $this->printTag($tag);
          break;
      }

      // let it be known whether we just issued an equation or not
      $equationMode = ($tag["type"] == "equation");
    }

    // end all pending lists, 
    $value .= str_repeat("</ul>", $depth);

    return $value;
  }
}

?>
