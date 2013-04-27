<?php

require_once("php/page.php");
require_once("php/general.php");

function printKeyValue($key, $value) {
  $output = ""; // TODO maybe using $output instead of $value is a better thing for all this string building...
  switch ($key) {
    case "url":
      $output .= "<tr><td><i>" . $key . "</i></td><td><a href='" . $value . "'>" . $value . "</a></td></tr>";
      break;

    case "name":
      // this should be ignored
      break;

    default:
      $output .= "<tr><td><i>" . $key . "</i></td><td>" . parseTeX($value) . "</td></tr>";
  }

  return $output;
}

class BibliographyPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>Bibliography</h2>";

    return $value;
  }
  public function getSidebar() {
    $output = "";

    return $output;
  }
  public function getTitle() {
    return "";
  }
}

class BibliographyItemPage extends Page {
  private $item;

  public function __construct($database, $name) {
    $this->db = $database;
    $this->item = getBibliographyItem($name);
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Bibliography item: <code>" . $this->item["name"] . "</code></h2>";
    $output .= "<table id='bibliography'>";
    // print these keys in this order
    $keys = array("author", "title", "year", "type");
    foreach ($keys as $key)
      $output .= printKeyValue($key, $this->item[$key]);

    foreach ($this->item as $key => $value) {
      if (!in_array($key, $keys))
        $output .= printKeyValue($key, $value);
    }
    $output .= "</table>";

    $output .= "<h2>BibTeX code</h2>";
    $output .= "<pre><code>";
    $output .= "TODO"; // TODO create BibTeX code from database information
    $output .= "</code></pre>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Navigation</h2>";
    $output .= "<p>Back to bibliography</p>";
    $output .= "<p class='navigation'><span class='left'>Previous item</span><span class='right'>Next item</span></p>";

    $output .= "<h2>Referencing tags</h2>";
    $output .= "<p>This item is referenced in</p>";

    return $output;
  }
  public function getTitle() {
    return "";
  }
}

?>


