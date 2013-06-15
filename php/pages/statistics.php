<?php

require_once("php/page.php");
require_once("php/general.php");

function printStatisticsRow($name, &$value, $remark = "") {
  $output = "";

  $output .= "<tr><td>" . $name . "</td><td>";
  if (isset($value))
    $output .= $value;
  else
    $output .= "<span style='text-style: italic'>?</span>";
  $output .= "</td><td>" . $remark . "</td>";

  return $output;
}

function getReferencingTags($target) {
  global $database;

  $sql = $database->prepare("SELECT source, type, book_id, name FROM dependencies, tags WHERE target = :target AND source = tag ORDER BY position");
  $sql->bindParam(":target", $target);

  if ($sql->execute())
    return $sql->fetchAll();

  return array();
}

class StatisticsPage extends Page {
  private $statistics;
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;

    $sql = $this->db->prepare("SELECT tag, creation_date, creation_commit, modification_date, modification_commit, label, position FROM tags WHERE tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->tag = $sql->fetch();

    // phantom is actually a chapter
    if (isPhantom($this->tag["label"]))
      $this->tag["type"] = "chapter";

    $sql = $this->db->prepare("SELECT key, value FROM statistics WHERE key LIKE :tag");
    $sql->bindValue(":tag", $tag . "%");

    if ($sql->execute())
      $result = $sql->fetchAll();

    foreach ($result as $row)
      $this->statistics[substr($row["key"], 5)] = $row["value"];
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

    $output .= "<h3>Numbers</h3>";
    $output .= "<p>The dependency graph has the following properties";
    $output .= "<table class='alternating' id='numbers'>";

    $referencingTags = getReferencingTags($this->tag["tag"]);

    $output .= printStatisticsRow("number of nodes", $this->statistics["node count"]);
    $output .= printStatisticsRow("number of edges", $this->statistics["edge count"], "(ignoring multiplicity)");
    $output .= printStatisticsRow("", $this->statistics["total edge count"], "(with multiplicity)");
    $output .= printStatisticsRow("number of chapters used", $this->statistics["chapter count"]);
    $output .= printStatisticsRow("number of sections used", $this->statistics["section count"]);
    if (count($referencingTags) > 0)
      $output .= printStatisticsRow("number of tags using this tag", $this->statistics["use count"], "(directly, see <a href='#referencing'>tags referencing this result</a>)");
    else
      $output .= printStatisticsRow("number of tags using this tag", $this->statistics["use count"], "(directly)");
    $output .= printStatisticsRow("", $this->statistics["indirect use count"], "(both directly and indirectly)");
    $output .= "</table>";

    if (count($referencingTags) > 0) {
      $output .= "<h3 id='referencing'>Tags using this result</h3>";
      $output .= "<ul id='using'>";
      $referencingTags = getReferencingTags($this->tag["tag"]);
      foreach ($referencingTags as $referencingTag) {
        $title = ucfirst($referencingTag["type"]) . " " . $referencingTag["book_id"];
        if ($referencingTag["name"] != "")
          $title .= ": " . parseAccents($referencingTag["name"]);
        
        $output .= "<li><a title=\"" . $title . "\" href='" . href("tag/" . $referencingTag["source"]) . "'><var>" . $referencingTag["source"] . "</var></a>";
      }
      $output .= "</ul>";
    }

    return $output;
  }
  public function getSidebar() {
    $output = "";

    // TODO some go to tag link

    $output .= "<h2>Navigating results</h2>";
    $siblingTags = getSiblingTags($this->tag["position"]);
    if (!empty($siblingTags)) {
      $output .= "<p class='navigation'>";
      if (isset($siblingTags["previous"]))
        $output .= "<span class='left'><a title='" . $siblingTags["previous"]["tag"] . " " . $siblingTags["previous"]["label"] . "' href='" . href("tag/" . $siblingTags["previous"]["tag"]) . "/statistics'>&lt;&lt; Previous tag</a></span>";
      if (isset($siblingTags["next"]))
        $output .= "<span class='right'><a title='" . $siblingTags["next"]["tag"] . " " . $siblingTags["next"]["label"] . "' href='" . href("tag/" . $siblingTags["next"]["tag"]) . "/statistics'>Next tag &gt;&gt;</a></span>";
      $output .= "</p>";
    }

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

