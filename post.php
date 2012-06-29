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

  // TODO all kinds of validation and escaping

  // from here on it's safe to ignore the fact that it's user input
  $tag = $_POST['tag'];
  $author = $_POST['name'];
  $email = $_POST['email'];
  $comment = $_POST['comment'];
  $website = $_POST['website']; // TODO either call it website or site but now it's inconsistent

  try {
    $sql = 'INSERT INTO comments (tag, author, comment, site) VALUES ("' . $tag . '", "' . $author . '", "' . $comment . '", "' . $website . '")';
    print($sql);
    $db->exec($sql) or die(print_r($db->errorInfo(), true));
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
  
  header('Location: /tag/' . $_POST['tag']);
?>
