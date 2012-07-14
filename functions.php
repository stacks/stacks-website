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

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function label_exists($label) {
  global $db;
  try {
    $sql = $db->prepare('SELECT COUNT(*) FROM tags WHERE label = :label');
    $sql->bindParam(':label', $label);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function position_exists($position) {
  global $db;
  try {
    $sql = $db->prepare('SELECT COUNT(*) FROM tags WHERE position = :position AND active = "TRUE"');
    $sql->bindParam(':position', $position);

    if ($sql->execute())
      return intval($sql->fetchColumn()) > 0;
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

    if ($sql->execute())
      return $sql->fetchColumn() == 'TRUE';
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}

function get_label($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT label FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute())
      return $sql->fetchColumn();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "";
}

function get_tag($tag) {
  assert(is_valid_tag($tag));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag, label, file, chapter_page, book_page, book_id, value, name, type, position FROM tags WHERE tag = :tag');
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

function get_tag_at($position) {
  assert(position_exists($position));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag, label FROM tags WHERE position = :position AND active = "TRUE"');
    $sql->bindParam(':position', $position);

    if ($sql->execute())
      return $sql->fetch();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "ZZZZ";
}

function get_tag_referring_to($label) {
  assert(label_exists($label));

  global $db;
  try {
    $sql = $db->prepare('SELECT tag FROM tags WHERE label = :label AND active = "TRUE"');
    $sql->bindParam(':label', $label);

    if ($sql->execute())
      return $sql->fetchColumn();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return "ZZZZ";
}

function latex_to_html($text) {
 $text = str_replace("\'E", "&Eacute;", $text);
 $text = str_replace("{\\v C}", "&#268;", $text);
 // this is to remedy a bug in import_titles
 $text = str_replace("\\v C}", "&#268;", $text);

 return $text;
}
?>
