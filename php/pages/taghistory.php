<?php

require_once("php/page.php");
require_once("php/general.php");

# link to a commit
function GitHubCommitLink($hash) {
  return "https://github.com/stacks/stacks-project/commit/" . $hash;
}

# link to a specific set of lines in a commit
function GitHubCommitLinesLink($change) {
  return "https://github.com/stacks/stacks-project/blob/" . $change["hash"] . "/" . $change["file"] . ".tex#L" . $change["begin"] . "-L" . $change["end"];
}

# link to a diff
function GitHubDiffLink($change) {
  # TODO this is broken at the moment, as I don't understand GitHub's link structure yet
  return "https://github.com/stacks/stacks-project/commit/" . $change["hash"] . "/" . $change["file"] . ".tex#L" . $change["begin"];
}

function printChange($tag, $change) {
  $output = "<tr>";

  $time = time($change["time"]);

  switch ($change["type"]) {
    case "creation":
      $output .= "<td>created statement";
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubCommitLinesLink($change) . "'>statement</a>";
      break;

    case "label":
      $output .= "<td>changed label";
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubCommitLinesLink($change) . "'>statement</a>";
      break;

    case "move":
      $output .= "<td>moved the tag";
      $output .= "<td>" . $time;
      break;

    case "proof":
    case "statement":
      $output .= "<td>change in " . $change["type"];
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubDiffLink($change) . "'>diff view</a>";
      break;

    case "tag":
      $output .= "<td>assigned a tag";
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubCommitLink($change["hash"]) . "'>GitHub</a>";
      break;
  }

  $output .= "</tr>";

  return $output;
}

class HistoryPage extends Page {
  private $changes;
  private $tag;

  public function __construct($database, $tag) {
    $this->db = $database;

    $sql = $this->db->prepare("SELECT tag, creation_date, creation_commit, modification_date, modification_commit, label, position FROM tags WHERE tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->tag = $sql->fetch();

    $sql = $this->db->prepare("SELECT changes.tag, changes.hash, changes.file, changes.type, changes.begin, changes.end, changes.label, commits.time FROM changes, commits WHERE commits.hash = changes.hash AND tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->changes = $sql->fetchAll();

    // phantom is actually a chapter
    if (isPhantom($this->tag["label"]))
      $this->tag["type"] = "chapter";

    $sql = $this->db->prepare("SELECT tag, node_count, edge_count, total_edge_count, chapter_count, section_count, use_count, indirect_use_count FROM graphs WHERE tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->graphs = $sql->fetch();
  }

  public function getHead() {
    $output = "";

    $output .= "<link rel='stylesheet' type='text/css' href='" . href("css/tag.css") . "'>";

    return $output;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Tag <var>" . $this->tag["tag"] . "</var></h2>";
    $output .= "<p>Go to the <a href='" . href("tag/" . $this->tag["tag"]) . "'>corresponding tag page</a>.</p>";

    $output .= "<table class='alternating history' id='numbers'>";
    $output .= "<thead>";
    $output .= "<tr>";
    $output .= "<td>type";
    $output .= "<td>time";
    $output .= "<td>link";
    $output .= "</tr>";
    $output .= "</thead>";
  
    foreach ($this->changes as $change)
      $output .= printChange($this->tag["tag"], $change);

    $output .= "</table>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    // TODO some go to tag link
    // TODO navigate this page too

    $output .= "<h2>Navigating history</h2>";
    $siblingTags = getSiblingTags($this->tag["position"]);
    if (!empty($siblingTags)) {
      $output .= "<p class='navigation'>";
      if (isset($siblingTags["previous"]))
        $output .= "<span class='left'><a title='" . $siblingTags["previous"]["tag"] . " " . $siblingTags["previous"]["label"] . "' href='" . href("tag/" . $siblingTags["previous"]["tag"]) . "/history'>&lt;&lt; Previous tag</a></span>";
      if (isset($siblingTags["next"]))
        $output .= "<span class='right'><a title='" . $siblingTags["next"]["tag"] . " " . $siblingTags["next"]["label"] . "' href='" . href("tag/" . $siblingTags["next"]["tag"]) . "/history'>Next tag &gt;&gt;</a></span>";
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

