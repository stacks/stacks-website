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

  public function getHead() {
    $output = "";

    $output .= "<link rel='stylesheet' type='text/css' href='" . href("css/tag.css") . "'>";

    return $output;
  }

  public function getMain() {
    $output = "";
    $output .= "<h2>Tag <var>" . $this->tag["tag"] . "</var></h2>";

    $output .= "<h3>Information on the label</h3>";
    $output .= "<p>This tag currently has the label <var>" . $this->tag["label"] . "</var>.";
    $output .= "<dl>";
    $output .= "<dt>Part of the Stacks project since</dt>";
    if ($this->tag["creation_date"] == "May 16 11:14:00 2009 -0400")
      $output .= "<dd>" . $this->tag["creation_date"] . " (see <a href='" . href("tags#stacks-epoch") . "'>Stacks epoch</a>) in <a href='https://github.com/stacks/stacks-project/commit/" . $this->tag["creation_commit"] . "'>commit " . substr($this->tag["creation_commit"], 0, 7) . "</a>.</dd>";
    else
      $output .= "<dd>" . $this->tag["creation_date"] . " in <a href='https://github.com/stacks/stacks-project/commit/" . $this->tag["creation_commit"] . "'>commit " . substr($this->tag["creation_commit"], 0, 7) . "</a>.</dd>";
    $output .= "<dt>Last modification to this label (not its contents)</dt>";
    $output .= "<dd>" . $this->tag["modification_date"] . " in <a href='https://github.com/stacks/stacks-project/commit/" . $this->tag["modification_commit"] . "'>commit " . substr($this->tag["modification_commit"], 0, 7) . "</a>.</dd>";
    $output .= "</dl>";
    # TODO this page needs more stuff, and a sidebar

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Dependency graphs</h2>";
    $output .= "<p style='margin-left: 1em'>" . printGraphLink($this->tag["tag"], "cluster", "cluster") . "<br>";
    $output .= printGraphLink($this->tag["tag"], "force", "force-directed") . "<br>";
    $output .= printGraphLink($this->tag["tag"], "collapsible", "collapsible") . "<br>";

    return $output;
  }
  public function getTitle() {
    return " &mdash; Statistics for the tag " . $this->tag["tag"];
  }
}

?>

