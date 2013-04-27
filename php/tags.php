<?php

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
