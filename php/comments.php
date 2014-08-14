<?php

function get_comments($db, $start, $number) {
  $stop = $start + $number;

  $sql = $db->prepare('SELECT id, tag, author, site, date, comment FROM comments ORDER BY date DESC LIMIT :start, :stop');
  $sql->bindParam(':start', $start);
  $sql->bindParam(':stop', $stop);

  if ($sql->execute())
    return $sql->fetchAll();
}

function getCommentsSidebar($db) {
  $comments = get_comments($db, 0, 6);

  $value = "";
  $maxAuthorLength = 15;

  $value .= "<h2><a href='" . href("recent-comments.xml") . "' class='rss'>Recent comments</a></h2>";
  $value .= "<ul>";

  foreach ($comments as $comment) {
    $date = new DateTime($comment["date"]);
    $value .= "<li>" . date_format($date, "j F Y, g:i a") . ":<br> ";
    if (strlen($comment["author"]) > $maxAuthorLength)
      $value .= htmlentities(mb_substr($comment["author"], 0, $maxAuthorLength, "UTF-8")) . "...";
    else
      $value .= htmlentities($comment["author"]);

    $tag = getTag($comment["tag"]);
    $section = getEnclosingSection($tag["position"]);
    $value .= " on <a title='in section " . $section["book_id"] ." " . parseAccents($section["name"]) . "' href='" . href("tag/" . $comment["tag"] . "#comment-" . $comment["id"]) . "'>tag " . $comment["tag"] . "</a>";
  }

  $value .= "</ul>";

  return $value;
}

?>
