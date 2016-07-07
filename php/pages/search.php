<?php

require_once("php/page.php");
require_once("php/general.php");

function getSearchPageHead() {
  global $config;
  $output = "";

  $output .= "<script type='text/javascript' src='" . $config["jQuery"] . "'></script>";
  $output .= "<script type='text/javascript' src='" . href("js/search.js") . "'></script>";
  $output .= "<link rel='stylesheet' type='text/css' href='" . href("css/search.css") . "'>";

  return $output;
}

function getSearchPageSidebar() {
  $output = "";

  $output .= "<h2>Tips and remarks</h2>";
  $output .= "<ul>";
  $output .= "<li>use wildcards, <code>ideal</code> doesn't match <code>ideals</code>, but <code>ideal*</code> matches both;";
  $output .= "<li>strings like <code>quasi-compact</code> should be enclosed by double quotes, otherwise you are looking for tags containing <code>quasi</code> but not <code>compact</code>;";
  $output .= "<li>if a search result corresponds to a (sub)section no preview option is given";
  $output .= "</ul>";

  return $output;
}

class SearchPage extends Page {
  public function getHead() {
    return getSearchPageHead();
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Search</h2>";
    $output .= SearchPage::getSearchForm();

    return $output;
  }
  public function getSidebar() {
    return getSearchPageSidebar();
  }
  public function getTitle() {
    return " &mdash; Search";
  }

  public static function getSearchForm($keywords = "", $options = array()) {
    $output = "";
    $output .= "<form id='search' method='get' action='" . href('search') . "'>
      <fieldset>
        <legend>Query</legend>
        <label for='keywords'>Keywords: <input type='text' id='keywords' size='35' name='keywords' value=\"" . htmlentities($keywords) . "\"></label>
        <label><input type='submit' id='submit' value='Search' /></label>
      </fieldset>

      <fieldset>
        <legend>Options</legend>
        <p>Limit your search to include:<br>";

    if (!array_key_exists("limit", $options)) {
      $output .= "
        <label for='limit-none'><input type='radio' value='all' name='limit' id='limit-none' checked='checked'> all tags</label><br>
        <label for='limit-sections'><input type='radio' name='limit' value='sections' id='limit-sections'> only complete sections</label><br>
        <label for='limit-statements'><input type='radio' name='limit' value='statements' id='limit-statements'> only statements, not the proofs</label>";
    }
    else {
      $output .= "
        <label for='limit-none'><input type='radio' value='all' name='limit' id='limit-none'" . ($options["limit"] == "all" ? " checked='checked'" : "") . "> all tags</label><br>
        <label for='limit-sections'><input type='radio' name='limit' value='sections' id='limit-sections'" . ($options["limit"] == "sections" ? " checked='checked'" : "") . "> only complete sections</label><br>
        <label for='limit-statements'><input type='radio' name='limit' value='statements' id='limit-statements'" . ($options["limit"] == "statements" ? " checked='checked'" : "") . "> only statements, not the proofs</label>";
    }

    $output .= "<p>If a query matches both a tag and a parent tag, only display the child:<br>";
    if (!array_key_exists("exclude-duplicates", $options)) {
      $output .= "<label for='exclude-duplicates'><input type='checkbox' name='exclude-duplicates' id='exclude-duplicates'> remove duplicates</label>";
    }
    else {
      $output .= "<label for='exclude-duplicates'><input type='checkbox' name='exclude-duplicates' id='exclude-duplicates' checked='checked'> remove duplicates</label>";
    }

    $output .= "</fieldset></form>";

    return $output;
  }
}

?>

