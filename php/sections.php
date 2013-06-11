<?php

function getChapter($chapter_id) {
  assert(sectionExists($chapter_id));
  global $database;

  $sql = $database->prepare('SELECT title, filename, number FROM sections WHERE number = :number');
  $sql->bindParam(':number', $chapter_id);

  if ($sql->execute())
    return $sql->fetch();

  return '';
}

function sectionExists($number) {
  assert(is_numeric($number));
  global $database;

  $sql = $database->prepare('SELECT COUNT(*) FROM sections WHERE number = :number');
  $sql->bindParam(':number', $number);

  if ($sql->execute())
    return intval($sql->fetchColumn()) > 0;

  return false;
}

?>
