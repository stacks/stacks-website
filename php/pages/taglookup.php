<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/search.php");

function printTagLookup($size = 20) {
  $output = "";

  $output .= "<form class='simple' action='" . href("lookup") . "' method='post'>";
  $output .= "<label><span>Tag:</span> <input type='text' name='tag' maxlength='4' size='" . $size . "'></label>";
  $output .= "<input type='submit' value='locate'>";
  $output .= "</form>";

  return $output;
}

function printReminder() {
  $value = "";

  $value .= "<h2><a href='" . href("tags") . "'>Reminder</a></h2>";
  $value .= "<p>Tags are unique identifiers of a specific result. Instead of using a reference like \"Lemma 12.8.4\" which is likely to change when results are added a tag is a <em>stable</em> way of referring to a result.</p>";
  $value .= "<p>Tags are 4 symbols long, using letters and digits.</p>";
  $value .= "<p>Some examples:</p>";
  $value .= "<pre><code><a href='" . href("tag/01ER") . "'>01ER</a></code>\n";
  $value .= "<code><a href='" . href("tag/02LS") . "'>02LS</a></code></pre>";
  $value .= "<p>For more information see <a href='" . href('tags') . "'>tags explained</a>.";

  return $value;
}

class TagLookupPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>Look for a tag</h2>";
    $value .= "<p>Look for a tag, if you have its 4-symbol code</p>";
    $value .= printTagLookup();
    $value .= "<p>For more information we refer to the <a href='" . href('tags') . "'>tags explained</a> page.";

    $value .= "<h2><a href='" . href("search") . "'>Search</a></h2>";
    $value .= "<p>Are you instead looking for the search functionality?</p>";
    $value .= getSimpleSearchForm(false);

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= printReminder();

    return $value;
  }
  public function getTitle() {
    return " &mdash; Look for a tag";
  }
}

?>
