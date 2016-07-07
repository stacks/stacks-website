<?php

require_once("php/page.php");
require_once("php/general.php");

function compareItems($a, $b) {
  if (!isset($a["author"])) return 1;
  if (!isset($b["author"])) return 1;
  return (strtoupper($a["author"]) < strtoupper($b["author"])) ? -1 : 1;
}

function printItem($name, $item) {
  if (!isset($item["author"]))
    $item["author"] = "<em>no author known</em>";
  if (!isset($item["title"]))
    $item["title"] = "<em>no title known</em>";

  return "<li>" . parseTeX($item['author']) . ", <a href='" . href('bibliography/' . $name) . "'>" . parseTeX($item['title']) . '</a>';
}

function printKeyValue($key, $value) {
  $output = ""; // TODO maybe using $output instead of $value is a better thing for all this string building...
  switch ($key) {
    case "url":
      $output .= "<tr><td><i>" . $key . "</i></td><td><a href='" . $value . "'>" . $value . "</a></td></tr>";
      break;
    case "eprint":
      // we assume that any eprint is actually arXiv
      $output .= "<tr><td><i>" . $key . "</i></td><td><a href='http://arxiv.org/abs/" . $value . "'>" . $value . "</a></td></tr>";

    case "name":
      // this should be ignored
      break;
    default:
      $output .= "<tr><td><i>" . $key . "</i></td><td>" . parseTeX($value) . "</td></tr>";
  }

  return $output;
}

function printKeyValueCode($key, $value) {
  switch ($key) {
    case "name":
    case "type":
      // these should be ignored
      break;
    default:
      // all others should be printed in the BibTeX code
      return "  " . $key . " = {" . $value . "},\n";
  }

  return;
}


class BibliographyPage extends Page {
  private $letters = array();
  private $items;

  public function __construct($database) {
    $this->db = $database;

    $this->items = $this->getItems();
    foreach ($this->items as $item) {
      if (isset($item["author"]))
        array_push($this->letters, strtoupper($item["author"][0]));
    }
    $this->letters = array_unique($this->letters);
  }

  public function getHead() {
    $output = "";

    $output .= "<link rel='stylesheet' type='text/css' href='" . href("css/bibliography.css") . "'>";
    $output .= printMathJax();

    return $output;
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Bibliography</h2>";
    $firstLetter = "";

    foreach ($this->items as $item) {
      // output headers per letter
      if (isset($item["author"]))
        $author = $item["author"];
	  else
        $author = $item["editor"];

      if ($firstLetter != strtoupper($author[0])) {
        if ($firstLetter != "") {
          $output .= "</ul>";
          $output .= "<p class='up'><a href='#'>go back up</a></p>";
        }

        $output .= "<h3 id='" . strtoupper($author[0]) . "'>" . strtoupper($author[0]) . "</h3><ul class='bibliography'>";
        $firstLetter = strtoupper($author[0]);
      }

      $output .= printItem($item['name'], $item);
    }
    $output .= "</ul>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Index</h2>";
    $output .= "<ol id='index'>";
    foreach ($this->letters as $letter)
      $output .= "<li><a href='#" . $letter . "'>" . $letter . "</a></li>";
    $output .= "<br style='clear: both'></ol>";

    $output .= "<h2>Statistics</h2>";
    $output .= "<p>There are currently " . getBibliographyItemCount($this->db) . " items in the bibliography.";

    return $output;
  }
  public function getTitle() {
    return " &mdash; Bibliography";
  }

  private function getItems() {
    $sql = $this->db->prepare('SELECT bibliography_items.name, bibliography_items.type, bibliography_values.key, bibliography_values.value FROM bibliography_items, bibliography_values WHERE bibliography_items.name = bibliography_values.name ORDER BY bibliography_items.name COLLATE NOCASE');
    // TODO when the database has been sanitized and all author values are of the form "last name, first name" we can sort on the last name

    if ($sql->execute()) {
      $rows = $sql->fetchAll();

      // this output is a mess, so we sanitize it
      $result = array();
      foreach ($rows as $row) {
        $result[$row['name']]['type'] = $row['type'];
        $result[$row['name']]['name'] = $row['name'];
        $result[$row['name']][$row['key']] = $row['value'];
      }

      // we don't want FDL in the bibliography
      unset($result["FDL"]);

      usort($result, "compareItems");

      return $result;
    }

    return null;
  }
}

class BibliographyItemPage extends Page {
  private $item;

  public function __construct($database, $name) {
    $this->db = $database;
    $this->item = getBibliographyItem($name);
  }

  public function getMain() {
    $output = "";

    // these keys are the most important ones, and should be treated in this order
    // TODO these should always be present, check this
    $keys = array("author", "title", "year", "type");

    $output .= "<h2>Bibliography item: <code>" . $this->item["name"] . "</code></h2>";
    $output .= "<table id='bibliography'>";
    foreach ($keys as $key) {
      if (isset($this->item[$key]))
        $output .= printKeyValue($key, $this->item[$key]);
    }
    foreach ($this->item as $key => $value) {
      if (!in_array($key, $keys))
        $output .= printKeyValue($key, $value);
    }
    $output .= "</table>";

    $output .= "<h2>BibTeX code</h2>";
    $output .= "<p>You can use the following code to cite this item yourself.</p>";
    $output .= "<pre><code>";
    $output .= "@" . $this->item["type"] . "{" . $this->item["name"] . ",\n";
    foreach ($keys as $key) {
      if (isset($this->item[$key]))
        $output .= printKeyValueCode($key, $this->item[$key]);
    }
    foreach ($this->item as $key => $value) {
      if (!in_array($key, $keys))
        $output .= printKeyValueCode($key, $value);
    }
    $output .= "}";
    $output .= "</code></pre>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Navigation</h2>";
    $output .= "<p><a href='" . href("bibliography") . "'>Back to bibliography</a></p>";
    $neighbours = $this->getNeighbouringItems();
    $output .= "<p class='navigation'>";
    if (!empty($neighbours["previous"]))
      $output .= "<span class='left'><a href='" . href("bibliography/" . $neighbours["previous"]) . "'>&lt;&lt; Previous item</a></span>";
    $output .= "&nbsp;"; // make sure paragraph is not empty for styling purposes
    if (!empty($neighbours["next"]))
      $output .= "<span class='right'><a href='" . href("bibliography/" . $neighbours["next"]) . "'>Next item &gt;&gt;</a></span>";
    $output .= "</p>";

    $output .= "<h2>Referencing tags</h2>";
    $referencingTags = $this->getReferencingTags();
    $output .= "<p>This item is referenced in " . count($referencingTags). " tag(s)</p>";
    $output .= "<ul>";
    foreach ($referencingTags as $tag) {
      if ($tag["type"] == "item")
        $output .= "<li><a href='" . href("tag/" . $tag["tag"]) . "'>" . ucfirst($tag["type"]) . " " . $tag["book_id"] . " of the enumeration on page " . $tag["book_page"] . "</a>";
      else
        $output .= "<li><a href='" . href("tag/" . $tag["tag"]) . "'>" . ucfirst($tag["type"]) . " " . $tag["book_id"] . ((!empty($tag["name"]) and $tag["type"] != "equation") ? ": " . parseAccents($tag["name"]) . "</a>" : "</a>");
    }
    $output .= "</ul>";

    return $output;
  }
  public function getTitle() {
    return "";
  }

  private function getReferencingTags() {
    $results = array();

    $query = 'SELECT tag, type, book_id, name FROM tags WHERE tags.value LIKE ' . $this->db->quote('%\cite{' . $this->item["name"] . '}%') . ' OR tags.value LIKE ' . $this->db->quote('%\cite[%]{' . $this->item["name"] . '}%') . ' ORDER BY position';

    foreach ($this->db->query($query) as $row)
      $results[] = $row;

    return $results;
  }

  // TODO: use ordering by author names
  private function getNeighbouringItems() {
    $results = array();

    $sql = $this->db->prepare("SELECT name FROM bibliography_items WHERE UPPER(name) < UPPER(:name) ORDER BY name COLLATE NOCASE DESC LIMIT 1");
    $sql->bindParam(":name", $this->item["name"]);

    if ($sql->execute())
      $results["previous"] = $sql->fetchColumn();

    $sql = $this->db->prepare("SELECT name FROM bibliography_items WHERE UPPER(name) > UPPER(:name) ORDER BY name COLLATE NOCASE LIMIT 1");
    $sql->bindParam(":name", $this->item["name"]);

    if ($sql->execute())
      $results["next"] = $sql->fetchColumn();

    return $results;
  }
}

?>


