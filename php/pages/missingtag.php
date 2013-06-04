<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/pages/taglookup.php");

// TODO this page could extend the ErrorPage class?
class MissingTagPage extends Page {
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;
    $this->tag = $tag;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Missing tag: <var>" . $this->tag . "</var></h2>";
    $output .= "<p>The tag you requested does not exist. This probably means that we haven't gotten that far yet. The last tag currently in the database is</p>"; // TODO print last tag

    $output .= "<h2>Look for a tag</h2>";
    $output .= printTagLookup();
    $output .= "<p>For more information we refer to the <a href='" . href('tags') . "'>tags explained</a> page.";

    $output .= "<h2><a href='" . href("search") . "'>Search</a></h2>";
    $output .= "<p>Are you instead looking for the search functionality?</p>";
    $output .= get_simple_search_form();

    return $output;
  }
  public function getSidebar() {
    $output = "";

    return $output;
  }
  public function getTitle() {
    return " &mdash; Missing tag";
  }
}

?>


