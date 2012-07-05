<?php

function is_valid_tag($tag) {
  return (bool) preg_match_all('/^[[:alnum:]]{4}$/', $tag, $matches) == 1;
}

function tag_exists($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT COUNT(*) FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute()) {
      return intval($sql->fetchColumn()) > 0;
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function tag_is_active($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT active FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute()) {
      return $sql->fetchColumn() == 'TRUE';
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function get_tag($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag, label, file, chapter_page, book_page, book_id, value FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute()) {
      // return first (= only) row of the result
      while ($row = $sql->fetch()) return $row;
    }
    return null;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}
?>
