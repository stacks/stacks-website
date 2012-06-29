<?php
  error_reporting(E_ALL);

  print("<pre>\n");
  print_r($_POST);

  # TODO database should be located outside of the web directory
  try {
    $db = new PDO('sqlite:stacks.sqlite');
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  $tag = '005E';
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
