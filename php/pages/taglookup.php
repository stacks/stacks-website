<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/search.php");

function printTagLookup() {
  $output = "";

  $output .= "<p>Look for a tag, if you have its 4-symbol code</p>";
  $output .= "<form action='#' method='post'>"; // TODO fix URL
  $output .= "<label>Tag: <input type='text' name='tag'></label>";
  $output .= "<input type='submit' value='locate'>";
  $output .= "</form>";

  return $output;
}

class TagLookupPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>Look for a tag</h2>";
    $value .= printTagLookup();
    $value .= "<p>For more information we refer to the <a href='" . href('tags') . "'>tags explained</a> page.";

    $value .= "<h2><a href='" . href("search") . "'>Search</a></h2>";
    $value .= "<p>Are you instead looking for the search functionality?</p>";
    $value .= get_simple_search_form();

    return $value;
  }
  public function getSidebar() {
    $value = "";
    $value .= "<h2><a href='" . href("tags") . "'>Reminder</a></h2>";
    $value .= "<p>Tags are identifiers, ...</p>";
    $value .= "<p>Some examples:</p>";
    $value .= "<pre><code>01ER</code></pre>";
    $value .= "<p>For more information see <a href='" . href('tags') . "'>tags explained</a>.";

    return $value;
  }
  public function getTitle() {
    return "";
  }
}

?>
