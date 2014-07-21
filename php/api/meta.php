<?php

require_once("../tags.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// read configuration file
$config = parse_ini_file("../../config.ini");

// initialize the global database object
try {
  $database = new PDO("sqlite:" . "../../" . $config["database"]);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

if (!isValidTag($_GET["tag"])) {
  print "This tag is not valid.";
  exit;
}

if (!tagExists($_GET["tag"])) {
  print "This tag does not exist.";
  exit;
}

$tag = getTag($_GET["tag"]);
$chapter = getEnclosingChapter($tag["position"]);
$section = getEnclosingSection($tag["position"]);

$result = array();
$result["type"] = $tag["type"];
$result["label"] = $tag["label"];
$result["chapter_page"] = $tag["chapter_page"];
$result["book_page"] = $tag["book_page"];
$result["book_id"] = $tag["book_id"];
$result["value"] = $tag["value"];
$result["slogan"] = $tag["slogan"];
$result["chapter_name"] = $chapter["name"];
$result["section_name"] = $section["name"];

print json_encode($result);

?>
