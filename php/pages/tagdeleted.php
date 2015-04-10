<?php

require_once("php/page.php");
require_once("php/general.php");

class TagDeletedPage extends Page {
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;
    $this->tag = $tag;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Inactive tag: <var>" . $this->tag . "</var></h2>";
    $output .= "<p>The tag you requested did at some point in time belong to the Stacks project, but it was removed. You might be able to find a clue in the <a href='https://github.com/stacks/stacks-project/blob/master/tags/tags'>tags file</a>.</p>";
    
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
    return " &mdash; The tag " . $this->tag . " has been deleted";
  }
}

?>


