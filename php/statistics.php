<?php
// get the line count for a file (the total line count has filename total)
function getLineCount($db, $filename) {
  try {
    $sql = $db->prepare("SELECT value FROM statistics WHERE key = :key");
    $sql->bindValue(":key", "linecount " . $filename);

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getInactiveTagCount($db) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE active = 'FALSE'");

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getSectionCount($db) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE type = 'section'");

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getChapterCount($db) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE label LIKE '%phantom'");
    // TODO check relationship between this chapter count and the browse page

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}


function getTagCount($db) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM tags");

    if ($sql->execute())
      return $sql->fetchColumn() - getChapterCount($db);
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getStatisticsSidebar($db) {
  $value = "";

  $value .= "<h2>Statistics</h2>";
  $value .= "<ul>";
  $value .= "<li>" . getLineCount($db, "total") . " lines of code";
  $value .= "<li>" . getTagCount($db) . " tags (" . getInactiveTagCount($db) . " inactive tags)";
  $value .= "<li>" . getSectionCount($db) . " sections";
  $value .= "<li>" . getChapterCount($db) . " chapters";
  $value .= "<li>3305 pages";
  $value .= "</ul>";

  return $value;
}
?>
