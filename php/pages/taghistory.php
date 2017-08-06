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
  # one has to put the md5 hash of the filename in the URL for some reason
  # observe that at the moment it's impossible to refer to ranges, and we can only refer the line number of the beginning / end, not the whole range, so if the begin / end is out of the diff range not much happens
  return "https://github.com/stacks/stacks-project/commit/" . $change["hash"] . "/" . $change["file"] . ".tex#diff-" . md5($change["file"] . ".tex") . "R" . $change["begin"];
}

function printChange($tag, $change) {
  $output = "<tr>";

  //$time = time($change["time"]);
  $time = $change["time"];
  $time = date_create($change["time"], timezone_open('GMT'));
  //$time = date_format($time, "F j, Y \a\\t g:i a e");
  $time = date_format($time, "F j, Y");

  switch ($change["type"]) {
    case "creation":
      $output .= "<td>created statement";
      $output .= "<td>in <code>" . $change["file"] . ".tex</code>";
      if ($change["label"] != "")
        $output .= "<br>label <code>" . $change["label"] . "</code>";
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubCommitLinesLink($change) . "'>link</a>";
      break;

    case "label":
      $output .= "<td>changed label";
      $output .= "<td>label <code>" . $change["label"] . "</code>";
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubCommitLinesLink($change) . "'>diff</a>";
      break;

    case "move":
      $output .= "<td>moved the tag";
      $output .= "<td>";
      $output .= "<td>" . $time;
      break;

    case "move file":
      $output .= "<td>moved the tag";
      $output .= "<td>to <code>" . $change["file"] . ".tex</code>";
      $output .= "<td>" . $time;
      break;

    case "proof":
    case "statement":
    case "statement and proof":
      $output .= "<td>changed " . $change["type"];
      $output .= "<td>";
      $output .= "<td>" . $time;
      $output .= "<td><a href='" . GitHubDiffLink($change) . "'>diff</a>";
      break;

    case "tag":
      $output .= "<td>assigned tag";
      $output .= "<td><var>" . $tag . "</var>";
      $output .= "<td>" . $time;
      $output .= "<td>";
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

    $sql = $this->db->prepare("SELECT tag, creation_date, creation_commit, modification_date, modification_commit, label, position, type FROM tags WHERE tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->tag = $sql->fetch();

    $sql = $this->db->prepare("SELECT changes.tag, changes.hash, changes.file, changes.type, changes.begin, changes.end, changes.label, commits.time FROM changes, commits WHERE commits.hash = changes.hash AND tag = :tag");
    $sql->bindParam(":tag", $tag);

    if ($sql->execute())
      $this->changes = $sql->fetchAll();

    // Remove the ghost "change in proof" that occurs after tag assignment
    // This should only be done if there can be a proof -- see the script
    // stacks-tools/historical/data.py
    if (in_array($this->tag["type"], array("lemma", "proposition", "theorem"))) {
      $index = -1;

      for ($i = 0; $i < sizeof($this->changes); $i++) {
        if ($this->changes[$i]["type"] == "tag")
          $index = $i;
      }

      if ($index != -1)
        array_splice($this->changes, $index + 1, 1);
    }

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

    if (in_array($this->tag["type"], array("item", "equation", "section", "subsection", "chapter")))
      $output .= "<p>For this type of tag (<var>" . $this->tag["type"] . "</var>) there is no historical data available.";
    else {
      $output .= "<table class='alternating history' id='numbers'>";
      $output .= "<thead>";
      $output .= "<tr>";
      $output .= "<th style='width: 40%'>type";
      $output .= "<th style='width: 35%'>";
      $output .= "<th style='width: 25%'>time";
      $output .= "<th style='width: 10%'>link";
      $output .= "</tr>";
      $output .= "</thead>";
      $output .= "<tbody>";
  
      for ($i = 0; $i < sizeof($this->changes); $i++) {
        if ($i+1 < sizeof($this->changes) && $this->changes[$i]["hash"] == $this->changes[$i+1]["hash"] && $this->changes[$i]["type"] == "statement" && $this->changes[$i+1]["type"] == "proof") {
          $this->changes[$i]["type"] = "statement and proof";
          $output .= printChange($this->tag["tag"], $this->changes[$i]);

          $i++;
        }
        else
          $output .= printChange($this->tag["tag"], $this->changes[$i]);
      }

      $output .= "</tbody>";
      $output .= "</table>";
    }

    return $output;
  }
  public function getSidebar() {
    $output = "";

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
    return " &mdash; History for the tag " . $this->tag["tag"];
  }
}

?>

