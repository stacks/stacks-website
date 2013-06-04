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

function getPageCount($db, $filename) {
  try {
    $sql = $db->prepare("SELECT value FROM statistics WHERE key = :key");
    $sql->bindValue(":key", "pagecount " . $filename);

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getBibliographyItemCount($db) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM bibliography_items");

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getTagsInFileCount($db, $filename) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE file = :filename");
    $sql->bindValue(":filename", $filename);

    if ($sql->execute())
      return $sql->fetchColumn();
    // else
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}

function getSectionsInFileCount($db, $filename) {
  try {
    $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE file = :filename AND type = 'section'");
    $sql->bindValue(":filename", $filename);

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
  $value .= "<p>The Stacks project now consists of</p>";
  $value .= "<ul>";
  $value .= "<li>" . getLineCount($db, "total") . " lines of code";
  $value .= "<li>" . getTagCount($db) . " tags (" . getInactiveTagCount($db) . " inactive tags)";
  $value .= "<li>" . getSectionCount($db) . " sections";
  $value .= "<li>" . getChapterCount($db) . " chapters";
  $value .= "<li>" . getPageCount($db, "book") . " pages";
  $value .= "</ul>";

  return $value;
}
?>
