<?php

# TODO add .htaccess to remove this from indexing!

# TODO fix path
require_once("../../stacks-website-new/php/tags.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// read configuration file
$config = parse_ini_file("../../stacks-website-new/config.ini"); # TODO config

// initialize the global database object
try {
  $database = new PDO("sqlite:" . $config["database"]);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

function startsWith($haystack, $needle) {
    return !strncmp($haystack, $needle, strlen($needle));
}

function removeProofs($content) {
  $lines = explode("\n", $content);
  $output = "";
  $inProof = false;

  foreach ($lines as $i => $line) {
    if (startsWith($line, "\begin{proof}"))
      $inProof = true;

    if (!$inProof)
      $output .= $line . "\n";

    if (startsWith($line, "\end{proof}"))
      $inProof = false;
  }

  return $output;
}

$tag = getTag($_GET["tag"]);

if (!array_key_exists("type", $_GET))
  $type = "statement";
else
  $type = $_GET["type"];

switch ($_GET["type"]) {
  case "full":
    print convertLaTeX($_GET["tag"], $tag["file"], $tag["value"]);
    break;
  case "statement":
    print convertLaTeX($_GET["tag"], $tag["file"], removeProofs($tag["value"]));
    break;
}
?>
