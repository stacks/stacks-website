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

function printKeyValueCode($key, $value) {
  $output = "";

  switch ($key) {
    case "name":
    case "type":
      // this should be ignored
      break;
    default:
      $output .= "  " . $key . " = {" . $value . "},\n";
      break;
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

    // these keys are the most important ones, and should be treated in this order
    // TODO these should always be present, check this
    $keys = array("author", "title", "year", "type");

    $output .= "<h2>Bibliography item: <code>" . $this->item["name"] . "</code></h2>";
    $output .= "<table id='bibliography'>";
    foreach ($keys as $key)
      $output .= printKeyValue($key, $this->item[$key]);
    foreach ($this->item as $key => $value) {
      if (!in_array($key, $keys))
        $output .= printKeyValue($key, $value);
    }
    $output .= "</table>";

    $output .= "<h2>BibTeX code</h2>";
    $output .= "<p>You can use the following code to cite this item yourself.</p>";
    // TODO add copy code
    $output .= "<pre><code>";
    $output .= "@" . $this->item["type"] . "{" . $this->item["name"] . ",\n";
    foreach ($keys as $key)
      $output .= printKeyValueCode($key, $this->item[$key]);
    foreach ($this->item as $key => $value) {
      if (!in_array($key, $keys))
        $output .= printKeyValueCode($key, $value);
    }
    $output .= "}";
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


