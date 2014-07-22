<?php

require_once("php/page.php");
require_once("php/general.php");

// the (trivial) preview parsing for search results
function parsePreview($preview) {
  // remove irrelevant new lines at the end
  $preview = trim($preview);
  // escape stuff
  $preview = htmlentities($preview);

  // but links should work: tag links are made up from alphanumeric characters, slashes, dashes and underscores, while the LaTeX label contains only alphanumeric characters and dashes
  $preview = preg_replace('/&lt;a href=&quot;([A-Za-z0-9\/\-]+)&quot;&gt;([A-Za-z0-9\-]+)&lt;\/a&gt;/', "<a href='" . href("") . "$1'>$2</a>", $preview);

  return $preview;
}

class SearchResultsPage extends Page {
  public function __construct($database, $options) {
    $this->db = $database;
    $this->options = $options;
  }

  public function getHead() {
    return getSearchPageHead();
  }

  public function getMain() {
    $output = "";

    $output .= "<h2>Search</h2>";
    $output .= SearchPage::getSearchForm($this->options["keywords"], $this->options);

    $results = $this->search($this->options);

    $output .= "<p>There are " . sizeof($results) . " results</p>";

    $output .= "<div id='allResults'><ul class='results'>";
    $chapter = 0;
    foreach ($results as $result) {
      $currentChapter = implode(".", array_slice(explode(".", $result["book_id"]), 0, 1));
      if ($result["type"] != "item" and $chapter != $currentChapter) {
        $chapter = $currentChapter;
        $chapterInformation = getChapter($currentChapter);
        $output .= "</ul>";
        $output .= "<h3>Chapter " . $currentChapter . ": " . parseAccents($chapterInformation["title"]) . "</h3>";
        $output .= "<ul class='results'>";
      }
      $output .= $this->printResult($result, false); // we never show the proofs in the preview, although it would be possible
    }
    $output .= "</div>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= getSearchPageSidebar();

    return $output;
  }
  public function getTitle() {
    return " &mdash; Search results for '" . htmlspecialchars($this->options["keywords"]) . "'";
  }

  private function printResult($result, $includeProofs) {
    $output = "";

    switch ($result["type"]) {
      case "item":
        $parent = getEnclosingTag($result["position"]);
        $section = getEnclosingSection($result["position"]);
    
        // enumeration can live in sections, hence we should take care of this sidecase
        if ($parent["tag"] == $section["tag"])
          $output .= "<li><p><a href='" . href("tag/" . $result["tag"]) . "'>" . ucfirst($result["type"]) . " " . $result["book_id"] . "</a> of the enumeration in <a href='" . href("tag/" . $section["tag"]) . "'>" . ucfirst($section["type"]) . " " . $section["book_id"] . "</a></p>";
        else
          $output .= "<li><p><a href='" . href("tag/" . $result["tag"]) . "'>" . ucfirst($result["type"]) . " " . $result["book_id"] . "</a> of the enumeration in <a href='" . href("tag/" . $parent["tag"]) . "'>" . ucfirst($parent["type"]) . " " . $parent["book_id"] . "</a> in <a href='" . href("tag/" . $section["tag"]) . "'>" . "Section " . $section["book_id"] . ": " . parseAccents($section["name"]) . "</a></p>";
        break;

      case "section":
        $output .= "<li><p><a href='" . href("tag/" . $result["tag"]) . "'>" . ucfirst($result["type"]) . " " . $result["book_id"] . ((!empty($result["name"]) and $result["type"] != "equation") ? ": " . parseAccents($result["name"]) . "</a>" : "</a>") . "</p>";
        break;

      default:
        $section = getEnclosingSection($result["position"]);
        $output .= "<li><p><a href='" . href("tag/" . $result["tag"]) . "'>" . ucfirst($result["type"]) . " " . $result["book_id"] . ((!empty($result["name"]) and $result["type"] != "equation") ? ": " . parseAccents($result["name"]) . "</a>" : "</a>") . " in <a href='" . href("tag/" . $section["tag"]) . "'>" . "Section " . $section["book_id"] . ": " . parseAccents($section["name"]) . "</a></p>";
        break;
    }
    
    if (!in_array($result["type"], array("section", "subsection"))) {
      if ($includeProofs)
        $output .= "<pre class='preview' id='text-" . $result["tag"] . "'>" . parsePreview($result["text"]) . "</pre>";
      else
        $output .= "<pre class='preview' id='text-" . $result["tag"] . "'>" . parsePreview($result["text_without_proofs"]) . "</pre>";
    }

    return $output;
  }

  private function search($options) {
    $results = array();

    // FTS queries don't work with PDO (or maybe: a) I didn't try hard enough, b) did something stupid)
    $query = "SELECT tags.tag, tags.label, tags.type, tags.book_id, tags_search.text, tags_search.text_without_proofs, tags.book_page, tags.name, tags.file, tags.position FROM tags_search, tags WHERE tags_search.tag = tags.tag AND tags.active = 'TRUE'";
    // the user doesn't want tags of the type section or subsection (which contain all the tags from that section)
    switch ($options["limit"]) {
      case "statements":
        $query .= " AND tags_search.text_without_proofs MATCH " . $this->db->quote($options["keywords"]);
        break;
      case "sections":
        $query .= " AND tags.TYPE IN ('section', 'subsection')";
        $query .= " AND tags_search.text MATCH " . $this->db->quote($options["keywords"]);
        break;
      case "all":
        $query .= " AND tags_search.text MATCH " . $this->db->quote($options["keywords"]);
        break;
    }

    $query .= " ORDER BY tags.position";

    foreach ($this->db->query($query) as $row)
      $results[] = $row;

    // remove duplicates if requested
    $tags = array();
    if (isset($options["exclude-duplicates"])) {
      foreach ($results as $result) {
        // there is already a parent tag in the result list, so we have to remove this one
        $parts = explode(".", $result["book_id"]);
        $parentTag = implode(".", array_splice($parts, 0, 2));

        if (in_array($parentTag, $tags))
          $tags = array_diff($tags, array($parentTag));

        array_push($tags, $result["book_id"]);
      }

      // filter the results based on the list of tags that we want to keep
      foreach ($results as $key => $result) {
        if (!in_array($result["book_id"], $tags))
          unset($results[$key]);
      }
    }

    return $results; 
  }
}

?>

