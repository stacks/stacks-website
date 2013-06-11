<?php

require_once("php/page.php");
require_once("php/general.php");

class NotFoundPage extends Page {
  private $message;

  public function __construct($message) {
    header("HTTP/1.0 404 Not Found");

    $this->message = $message;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Error 404: page not found</h2>";
    $output .= $this->message;

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Tag lookup</h2>";
    $output .= printTagLookup(10);
    $output .= "<p style='clear: both'>";
    $output .= "<h2>Search</h2>";
    $output .= getSimpleSearchForm(10);

    return $output;
  }
  public function getTitle() {
    return " &mdash; Not found";
  }
}

?>


