<?php

function bibliographyItemExists($name) {
  global $database;

  $sql = $database->prepare('SELECT COUNT(*) FROM bibliography_items WHERE name = :name');
  $sql->bindParam(':name', $name);

  if ($sql->execute())
    return intval($sql->fetchColumn()) > 0;
  else  
    return false;
}

function getBibliographyItem($name) {
  assert(bibliographyItemExists($name));
  global $database;

  $sql = $database->prepare('SELECT bibliography_items.type, bibliography_values.key, bibliography_values.value, bibliography_items.name FROM bibliography_items, bibliography_values WHERE bibliography_items.name = :name AND bibliography_items.name = bibliography_values.name');
  $sql->bindParam(':name', $name);

  if ($sql->execute()) {
    $rows = $sql->fetchAll();

    // this output is a mess, sanitize it
    $result = array();
    foreach ($rows as $row) {
      $result['type'] = $row['type'];
      $result[$row['key']] = $row['value'];
      $result["name"] = $row["name"];
    }

    return $result;
  }

  return null;
}
