<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// read configuration file
$config = parse_ini_file("../../config.ini");

// initialize the global database object
try {
  $database = new PDO("sqlite:../../" . $config["database"]);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

// specific request for slogan information
if (isset($_GET["slogan"])) {
}
// random slogan is requested
else {
  print getRandomTag();
}

// return a random tag from the database
function getRandomTag() {
  global $database;

  try {
    $sql = $database->prepare("SELECT tag FROM tags ORDER BY RANDOM() LIMIT 1");
  
    if ($sql->execute()) {
      $tag = $sql->fetchAll();

      return $tag[0]["tag"];
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "0000";
}

?>
