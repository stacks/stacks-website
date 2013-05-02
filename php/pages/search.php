<?php

require_once("php/page.php");
require_once("php/general.php");

class SearchPage extends Page {
  public function getHead() {
    global $jQuery;
    $output = "";

    $output .= "<script type='text/javascript' src='" . $jQuery . "'></script>";
    $output .= "<script type='text/javascript' src='" . href("js/search.js") . "'></script>";
    $output .= "<link rel='stylesheet' type='text/css' href='" . href("css/search.css") . "'>";

    return $output;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Search</h2>";
    $output .= SearchPage::getSearchForm();

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Tips</h2>";
    $output .= "<ul>";
    $output .= "<li>use wildcards, <code>ideal</code> doesn't match <code>ideals</code>, but <code>ideal*</code> matches both;";
    $output .= "<li>strings like <code>quasi-compact</code> should be enclosed by double quotes, otherwise you are looking for tags containing <code>quasi</code> but not <code>compact</code>;";
    $output .= "</ul>";

    return $output;
  }
  public function getTitle() {
    return "";
  }

  public function getSearchForm($keywords = "") {
    $output = "";
    $output .= "<form id='search' method='get' action='" . href('search') . "'>
      <fieldset>
        <legend>Query</legend>
        <label for='keywords'>Keywords: <input type='text' id='keywords' size='35' name='keywords' value='" . $keywords . "'></label>
        <label><input type='submit' id='submit' value='Search' /></label>
      </fieldset>

      <fieldset>
        <legend>Options</legend>
        <p>Limit your search to include:<br>
        <label for='limit-none'><input type='radio' name='limit' id='limit-none' checked='checked'> all tags</label><br>
        <label for='limit-sections'><input type='radio' name='limit' id='limit-sections'> only complete sections</label><br>
        <label for='limit-statements'><input type='radio' name='limit' id='limit-statements'> only statements, not the proofs</label>

        <p>If a query matches both a tag and a parent tag, only display the child:<br>
        <label for='exclude-duplicates'><input type='checkbox' name='exclude-duplicates' id='exclude-duplicates'> remove duplicates</label>
      </fieldset>
    </form>";

    return $output;
  }
}

?>

