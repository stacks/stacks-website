<?php

function get_comments($db, $start, $number) {
  $stop = $start + $number;

  $sql = $db->prepare('SELECT id, tag, author, site, date, comment FROM comments ORDER BY date DESC LIMIT :start, :stop');
  $sql->bindParam(':start', $start);
  $sql->bindParam(':stop', $stop);

  if ($sql->execute())
    return $sql->fetchAll();
}

?>
