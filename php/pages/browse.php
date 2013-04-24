<?php

require_once("php/page.php");
require_once("php/general.php");

class BrowsePage extends Page {
  public function getHead() {
    return "<link rel='stylesheet' type='text/css' href='" . href("css/browse.css") . "'>";
  }

  public function getMain() {
    $value = "";

    $value .= "<h2>Browse chapters</h2>";

    // mapping the first chapter of each part to the title of the part
    $parts = array(
      "Introduction"                    => "Preliminaries",
      "Schemes"                         => "Schemes",
      "Chow Homology and Chern Classes" => "Topics in Scheme Theory",
      "Algebraic Spaces"                => "Algebraic Spaces",
      "Formal Deformation Theory"       => "Deformation Theory",
      "Algebraic Stacks"                => "Algebraic Stacks",
      "Examples"                        => "Miscellany");
    $number = 0;

    $value .= "<table id='browse'>";
    $value .= "<tr>";
    $value .= "<th>Part</th>";
    $value .= "<th>Chapter</th>";
    $value .= "<th>online</th>";
    $value .= "<th>TeX</th>";
    $value .= "<th>pdf</th> ";
    $value .= "<th>dvi</th>";
    $value .= "</tr>";

    try {
      $sql = $this->db->prepare("SELECT number, title, filename FROM sections WHERE number NOT LIKE '%.%' ORDER BY CAST(number AS INTEGER)");
      if ($sql->execute()) {
        while ($row = $sql->fetch()) {
          // check wheter it's the first chapter, insert row with part if necessary
          if (array_key_exists($row["title"], $parts)) {
            $value .= $this->printPart($parts[$row["title"]]); // TODO latex_to_html
          }

          // change LaTeX escaping to HTML escaping
          $value .= $this->printChapter($row["title"], $row["filename"], $row["number"]); // TODO latex_to_html
          // this->printChapter(latex_to_html($row["title"]), $row["filename"], $row["number"]);
          $number = $row["number"];
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    $value .= "</table>";

    return $value;
  }
  public function getSidebar() {
    // TODO print parts in the sidebar
    $value = "";

    return $value;
  }
  public function getTitle() {
    return "";
  }

  // print a row of the table containing a chapter
  private function printChapter($chapter, $filename, $number) {
    $value = "";

    $value .= "<tr>";
    // first column
    $value .= "<td></td>";
    // second column
    $value .= "<td>" . $number . ".&nbsp;&nbsp;&nbsp;" . $chapter . "</td>";
    // third column
    if ($chapter == "Bibliography")
      $value .= "<td><a href='" . href('bibliography') . "'><code>online</code></a></td>";
    else
      $value .= "<td><a href='" . href('chapter/' . $number) . "'><code>online</code></a></td>";
    // fourth column
    if ($chapter == "Auto generated index")
      $value .= "<td></td>";
    elseif ($chapter == "Bibliography")
      $value .= "<td><a href='" . href('tex/my.bib') . "'><code>tex</code></a></td>"; // TODO link to GitHub
    else
      $value .= "<td><a href='" . href('tex/' . $filename . '.tex') . "'><code>tex</code></a></td>"; // TODO link to GitHub
    // fifth column
    // TODO maybe just drop dvi? is this still actively being downloaded?
    if ($chapter == "Bibliography") {
      $value .= "<td><a href='" . href('download/bibliography.pdf') . "'><code>pdf</code></a></td>";
      $value .= "<td><a href='" . href('download/bibliography.dvi') . "'><code>dvi</code></a></td>";
    }
    else {
      $value .= "<td><a href='" . href('download/' . $filename . '.pdf') . "'><code>pdf</code></a></td>";
      $value .= "<td><a href='" . href('download/' . $filename . '.dvi') . "'><code>dvi</code></a></td>";
    }
    $value .= "</tr>";

    return $value;
  }

  // print a row of the table containing a part
  private function printPart($part) {
    $value = "";

    $value .= "<tr>";
    $value .= "<td>" . $part . "</td>";
    $value .= "<td></td>";
    $value .= "<td></td>";
    $value .= "<td></td>";
    $value .= "<td></td>";
    $value .= "<td></td>";
    $value .= "</tr>";

    return $value;
  }
}
