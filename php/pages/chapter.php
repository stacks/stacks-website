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

    $value .= "<script type='text/javascript' src='" . href('jquery-treeview/jquery.treeview.js') . "'></script>";
    $value .= "<link rel='stylesheet' href='" . href('js/jquery-treeview/jquery.treeview.css') . "' />";

    return $value;
  }

  public function getMain() {
    $value = "";

    $value .= "<h2>Tree view for Chapter " . $this->chapter["number"] . ": " . $this->chapter["title"] . "</h2>";
    $value .= $this->printNavigation();

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= "<h2>Navigating chapters</h2>";
    $value .= $this->printNavigation(false);

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
      $previous_chapter_information = get_chapter(intval($this->chapter['number']) - 1);
      $value .= "<span class='left'><a href='" . href('chapter/' . (intval($this->chapter['number']) - 1)) . "'>";
      if ($displayTitle)
        $value .= "&lt;&lt; Chapter " . (intval($this->chapter['number']) - 1) . ": " . latex_to_html($previous_chapter_information['title']);
      else
        $value .= "&lt;&lt; Previous chapter";
      $value .= "</a></span>";
    }
    // forward
    if (section_exists(intval($this->chapter['number']) + 1)) {
      $next_chapter_information = get_chapter(intval($this->chapter['number']) + 1);
      $value .= "<span class='right'><a href='" . href('chapter/' . (intval($this->chapter['number']) + 1)) . "'>";
      if ($displayTitle)
        $value .= "Chapter " . (intval($this->chapter['number']) + 1) . ": " . latex_to_html($next_chapter_information['title']) . " &gt;&gt;";
      else
        $value .= "Next chapter &gt;&gt;";
      $value .= "</a></span>"; 
    }

    return $value;
  }
}

?>

