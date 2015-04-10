<?php

require_once("php/page.php");
require_once("php/general.php");

class InvalidTagPage extends Page {
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;
    $this->tag = $tag;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Invalid tag: <var>" . htmlentities($this->tag) . "</var></h2>";
    $output .= "<p>This is not a well-formed tag. Tags are 4 symbols long and consist of letters and digits.</p>";
    
    $output .= "<h2>Look for a tag</h2>";
    $output .= printTagLookup();
    $output .= "<p>For more information we refer to the <a href='" . href('tags') . "'>tags explained</a> page.";

    $output .= "<h2><a href='" . href("search") . "'>Search</a></h2>";
    $output .= "<p>Are you instead looking for the search functionality?</p>";
    $output .= getSimpleSearchForm(false);

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= printReminder();

    return $output;
  }
  public function getTitle() {
    return " &mdash; The tag " . htmlentities($this->tag) . " is invalid";
  }
}

?>


