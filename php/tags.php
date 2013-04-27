<?php

function getEnclosingTag($position) {
  assert(positionExists($position));

  global $database;

  try {
    $sql = $database->prepare("SELECT tag, type, book_id FROM tags WHERE position < :position AND active = 'TRUE' AND type != 'item' ORDER BY position DESC LIMIT 1");
    $sql->bindParam(":position", $position);

    if ($sql->execute())
      return $sql->fetch();
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  // TODO this should do more
  return "ZZZZ";
}

function getTagAtPosition($position) {
  assert(positionExists($position));

  global $database;

  try {
    $sql = $database->prepare("SELECT tag, label FROM tags WHERE position = :position AND active = 'TRUE'");
    $sql->bindParam(":position", $position);

    if ($sql->execute())
      return $sql->fetch();
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  // TODO more
  return "ZZZZ";
}

function positionExists($position) {
  global $database;

  try {
    $sql = $database->prepare("SELECT COUNT(*) FROM tags WHERE position = :position AND active = 'TRUE'");
    $sql->bindParam(":position", $position);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
    // TODO error handling
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

?>
