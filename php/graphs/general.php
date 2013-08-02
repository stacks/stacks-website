<?php

require_once("../tags.php");

try {
  $database = new PDO("sqlite:../../" . $config["database"]);
  $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
  print "Something went wrong with the database. If the problem persists, please contact us at <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>.";
  // if there is actually a persistent error: add output code here to check it
  print_r($e);
  exit();
}

function graphExists($tag, $type) {
  $filename = "../../data/" . strtoupper($tag) . "-" . $type . ".json";

  return file_exists($filename);
}

function printInvalidTag($tag) {
  print "The tag " . htmlentities($tag) . " is not a valid tag.";
  print "<br><a href='#' onclick='history.go(-2);'>Go back</a>";
}

function printInexistingTag($tag) {
  print "The graph you requested (with tag " . htmlentities($tag) . ") does not exist.";
  print "<br><a href='#' onclick='history.go(-2);'>Go back</a>";
}

function printInexistingGraph() {
  print "There is no data available for this graph.";
  print "<br><a href='#' onclick='history.go(-2);'>Go back</a>";
}

function tagForGraphCheck($tag, $type) {
  if (!isValidTag(strtoupper($_GET["tag"]))) {
    printInvalidTag($_GET["tag"]);
    exit();
  }
  
  if (!tagExists(strtoupper($_GET["tag"]))) {
    printInexistingTag($_GET["tag"]);
    exit();
  }
  
  if (!graphExists(strtoupper($_GET["tag"]), $type)) {
    printInexistingGraph();
    exit();
  }
}

?>
