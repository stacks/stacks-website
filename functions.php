<?php

function is_valid_tag($tag) {
  return (bool) preg_match_all('/^[[:alnum:]]{4}$/', $tag, $matches) == 1;
}

function tag_exists($tag) {
  assert(is_valid_tag($tag));

  // TODO there must be better ways, COUNT in SQL, or at least not using foreach (also applies to get_tag)
  global $db;
  try {
    $sql = $db->prepare('SELECT tag FROM tags WHERE tag = :tag');
    $sql->bindParam(':tag', $tag);

    if ($sql->execute()) {
      while ($row = $sql->fetch()) return true;
    }
    return false;
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  return false;
}


?>
