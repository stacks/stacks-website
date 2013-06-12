<?php

require_once("php/page.php");
require_once("php/general.php");

class StatisticsPage extends Page {
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;

    $sql = $this->db->prepare("SELECT tag, creation_date, creation_commit, modification_date, modification_commit, label FROM tags WHERE tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->tag = $sql->fetch();

    // phantom is actually a chapter
    if (isPhantom($this->tag["label"]))
      $this->tag["type"] = "chapter";
  }
  public function getMain() {
    $output = "";
    $output .= "<h2>Tag <var>" . $this->tag["tag"] . "</var></h2>";

    $output .= "<dl>";
    $output .= "<dt>Creation of this tag</dt>";
    $output .= "<dd>" . $this->tag["creation_date"] . " in <a href='https://github.com/stacks/stacks-project/commit/" . $this->tag["creation_commit"] . "'>commit " . substr($this->tag["creation_commit"], 0, 7) . "</a></dd>";
    $output .= "<dt>Last modification</dt>";
    $output .= "<dd>" . $this->tag["modification_date"] . " in <a href='https://github.com/stacks/stacks-project/commit/" . $this->tag["modification_commit"] . "'>commit " . substr($this->tag["modification_commit"], 0, 7) . "</a></dd>";
    $output .= "</dl>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    return $output;
  }
  public function getTitle() {
    return " &mdash; Statistics for the tag " . $this->tag["tag"];
  }
}

?>

