<?php
// get the line count for a file (the total line count has filename total)
function getLineCount($db, $filename) {
  $sql = $db->prepare("SELECT value FROM statistics WHERE key = :key");
  $sql->bindValue(":key", "linecount " . $filename);

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getPageCount($db, $filename) {
  $sql = $db->prepare("SELECT value FROM statistics WHERE key = :key");
  $sql->bindValue(":key", "pagecount " . $filename);

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getBibliographyItemCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM bibliography_items");

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getTagsInFileCount($db, $filename) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE file = :filename");
  $sql->bindValue(":filename", $filename);

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getSectionsInFileCount($db, $filename) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE file = :filename AND type = 'section'");
  $sql->bindValue(":filename", $filename);

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getInactiveTagCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE active = 'FALSE'");

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getSectionCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE type = 'section'");

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getChapterCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE label LIKE '%phantom'");

  if ($sql->execute())
    return $sql->fetchColumn() - 1;
}

function getTagCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags");

  if ($sql->execute())
    return $sql->fetchColumn() - getChapterCount($db);
}

function getSubmittedSloganCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM slogans");

  if ($sql->execute())
    return $sql->fetchColumn();
}

function getActiveSloganCount($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM tags WHERE slogan != ''");

  if ($sql->execute())
    return $sql->fetchColumn();
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
  $value .= "<li>" . getActiveSloganCount($db) . " slogans"; // . " slogans (" . getSubmittedSloganCount($db) . " submitted)";
  $value .= "</ul>";

  return $value;
}
?>
