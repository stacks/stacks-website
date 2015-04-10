<?php

require_once("php/page.php");
require_once("php/general.php");

class ErrorPage extends Page {
  private $exception;

  public function __construct($exception) {
    header("HTTP/1.0 404 Not Found");

    $this->exception = $exception;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Something went wrong</h2>";
    $output .= "<pre><code>" . $this->exception->getMessage() . "</code></pre>";
    $output .= "<p>If you see this, please contact the Stacks project maintainers at <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>.";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    return $output;
  }
  public function getTitle() {
    return " &mdash; Error";
  }
}

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
    $output .= getSimpleSearchForm(false, 10);

    return $output;
  }
  public function getTitle() {
    return " &mdash; Not found";
  }
}

?>


