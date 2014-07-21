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
    $sql = $database->prepare("SELECT tags.tag, tags.type, tags.name, graphs.indirect_use_count, tags.slogan FROM tags, graphs WHERE tags.tag = graphs.tag");
  
    if ($sql->execute()) {
      $tags = $sql->fetchAll();

      // total of all the weights
      $sum = 0.0;
      // non-zero base weight for a tag we wish to take into account
      $base = 1.0;

      // determine weights for each tag
      foreach ($tags as &$tag) {
        $score = 0.0;

        switch ($tag["type"]) {
          case "lemma":
            $score = $base + log($tag["indirect_use_count"] + 1);
            break;
          case "proposition":
            $score = $base + 1.5 * log($tag["indirect_use_count"] + 1); // we score propositions a bit higher
            break;
          case "remark":
          case "remarks":
            $score = $base; // remarks don't have a meaningful use count
            break;
          case "theorem":
            $score = $base + 2.0 * log($tag["indirect_use_count"] + 1); // we score theorems higher
            break;
          // we don't consider these
          case "definition":
          case "equation":
          case "example":
          case "item":
          case "section":
          case "situation":
          case "subsection":
          default:
            break;
        }

        // named tags are more important
        if ($tag["name"] != "")
          $score = 2.0 * $score;

        // tags with a slogan should not appear too much
        if ($tag["slogan"] != "")
          $score = 0.05 * $score;

        $tag["score"] = $score;

        // keep track of the total
        $sum = $sum + $tag["score"];
      }

      // pick a random float between 0 and the sum of all weights
      $choice = lcg_value() * $sum;

      // look for the last tag such that the sum of all preceding weights is smaller than the choice
      $sum = 0.0;
      foreach ($tags as $tag) {
        if ($sum + $tag["score"] > $choice)
          return $tag["tag"];
        else
          $sum = $sum + $tag["score"];
      }
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "0000";
}

?>
