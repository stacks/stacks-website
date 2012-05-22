<?php
  error_reporting(E_ALL);

  print("<pre>\n");

  # TODO database should be located outside of the web directory
  try {
    $db = new PDO('sqlite:stacks.sqlite');
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  $tag = '005E';

  # TODO escape everything!
  try {
    foreach ($db->query('SELECT id, tag, author, date, comment, site FROM comments WHERE tag = "' . $tag . '"') as $row) {
      print("    <cite class='comment-author'>" . $row['author'] . "</cite>");
      print(" (<a href='" . $row['site'] . "'>website</a>)\n");
      print("    <span class='comment-date'>" . $row['date'] . "</span>\n");
      print("    <blockquote>" . $row['comment'] . "</blockquote>\n\n");
      print_r($row);
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  $author = 'Pieter Belmans';
  $comment = 'Inserting a comment, testing math: $\mathfrak{p}\in\operatorname{Spec}(A)$';
  $site = 'http://pbelmans.wordpress.com';

  try {
    $sql = 'INSERT INTO comments (tag, author, comment, site) VALUES ("' . $tag . '", "' . $author . '", "' . $comment . '", "' . $site . '")';
    # $db->exec($sql) or die(print_r($db->errorInfo(), true));
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

?>
