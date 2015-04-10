<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/pages/taglookup.php");

function getLastTag() {
  global $database;

  $sql = $database->prepare('SELECT tag FROM tags ORDER BY tag DESC');

  if ($sql->execute())
    return $sql->fetchColumn();

  return null;
}

// we do not use the NotFoundPage as a missing tag will eventually be filled in and we don't want to confuse crawlers
class MissingTagPage extends Page {
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;
    $this->tag = $tag;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Missing tag: <var>" . $this->tag . "</var></h2>";
    $lastTag = getLastTag();
    $output .= "<p>The tag you requested does not exist. This probably means that we haven't gotten that far yet. The last tag currently in the database is <a href='" . href("tag/" . $lastTag) . "'>tag <var>" . $lastTag . "</var></a>.";

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

    return $output;
  }
  public function getTitle() {
    return " &mdash; Missing tag";
  }
}

?>


