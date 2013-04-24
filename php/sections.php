<?php

function get_chapter($chapter_id) {
  assert(section_exists($chapter_id));

  global $database;
  try {
    $sql = $database->prepare('SELECT title, filename, number FROM sections WHERE number = :number');
    $sql->bindParam(':number', $chapter_id);

    if ($sql->execute())
      return $sql->fetch();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return '';
}

function section_exists($number) {
  assert(is_numeric($number));

  global $database;
  try {
    $sql = $database->prepare('SELECT COUNT(*) FROM sections WHERE number = :number');
    $sql->bindParam(':number', $number);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

?>
