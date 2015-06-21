<?php

require_once("php/page.php");
require_once("php/general.php");

// turn the name of a part into an identifier that is more HTMLish
function partToIdentifier($part) {
  return strtolower(str_replace(" ", "-", $part));
}

class BrowsePage extends Page {
  private $parts;

  public function __construct($database) {
    global $parts;

    $this->db = $database;

    $this->parts = $parts;
  }

  public function getHead() {
    return "<link rel='stylesheet' type='text/css' href='" . href("css/browse.css") . "'>";
  }

  public function getMain() {
    $value = "";

    $value .= "<h2>Browse chapters</h2>";
    $number = 0;

    $value .= "<table class='alternating' id='browse'>";
    $value .= "<tr>";
    $value .= "<th>Part</th>";
    $value .= "<th>Chapter</th>";
    $value .= "<th>online</th>";
    $value .= "<th>TeX source</th>";
    $value .= "<th>view pdf</th> ";
    $value .= "</tr>";

    $sql = $this->db->prepare("SELECT number, title, filename FROM sections WHERE number NOT LIKE '%.%' ORDER BY CAST(number AS INTEGER)");
    if ($sql->execute()) {
      while ($row = $sql->fetch()) {
        // check whether it's the first chapter, insert row with part if necessary
        if (array_key_exists($row["title"], $this->parts)) {
          $value .= $this->printPart($this->parts[$row["title"]]);
        }

        // change LaTeX escaping to HTML escaping
        $value .= $this->printChapter($row["title"], $row["filename"], $row["number"]);
        $number = $row["number"];
      }
    }

    $value .= "</table>";

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= "<h2>Parts</h2>";
    $value .= "<ol>";
    foreach ($this->parts as $part) {
      $value .= "<li><a href='#" . partToIdentifier($part) . "'>" . $part . "</a>";
    }
    $value .= "</ol>";

    $value .= getStatisticsSidebar($this->db);

    return $value;
  }
  public function getTitle() {
    return " &mdash; Chapters";
  }

  // print a row of the table containing a chapter
  private function printChapter($chapter, $filename, $number) {
    $value = "";

    $value .= "<tr>";
    // first column
    $value .= "<td style='width: 8em'></td>";
    // second column
    $value .= "<td>" . $number . ".&nbsp;&nbsp;&nbsp;" . parseAccents($chapter) . "</td>";
    // third column
    if ($chapter == "Bibliography")
      $value .= "<td class='download'><a href='" . href('bibliography') . "'>online</a></td>";
    elseif ($chapter == "Auto generated index")
      $value .= "<td></td>";
    else
      $value .= "<td class='download'><a href='" . href('chapter/' . $number) . "'>online</a></td>";
    // fourth column
    if ($chapter == "Auto generated index")
      $value .= "<td></td>";
    elseif ($chapter == "Bibliography")
      $value .= "<td class='download'><a href='https://github.com/stacks/stacks-project/blob/master/my.bib'><code>tex</code></a></td>";
    else
      $value .= "<td class='download'><a href='https://github.com/stacks/stacks-project/blob/master/" . $filename . ".tex'><code>tex</code></a></td>";
    // fifth column
    if ($chapter == "Bibliography")
      $value .= "<td class='download'><a href='" . href('download/bibliography.pdf') . "'><code>pdf</code></a></td>";
    else 
      $value .= "<td class='download'><a href='" . href('download/' . $filename . '.pdf') . "'><code>pdf</code></a></td>";
    $value .= "</tr>";

    return $value;
  }

  // print a row of the table containing a part
  private function printPart($part) {
    $value = "";

    $value .= "<tr id='" . partToIdentifier($part) . "'>";
    $value .= "<td colspan='2'>" . parseAccents($part) . "</td>";
    $value .= "<td></td>";
    $value .= "<td></td>";
    $value .= "<td></td>";
    $value .= "</tr>";

    return $value;
  }
}
