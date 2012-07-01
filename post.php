<?php
  include('config.php');
  include('functions.php');

  # TODO database should be located outside of the web directory
  try {
    $db = new PDO('sqlite:stacks.sqlite');
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  // TODO all kinds of validation and escaping
  // if this triggers the user is messing with the POST request
  if (!is_valid_tag($_POST['tag'])) {
    print('The tag your browser supplied in the request is not in a valid format.');
    exit();
  }
  // the tag is not present in the database, when we start handling removed tags this will have to change
  if (!tag_exists($_POST['tag'])) {
    print('The tag you are trying to post a comment on does not exist.');
    exit();
  }
  // empty author
  if (empty($_POST['name'])) {
    print('You should supply your name.');
    exit();
  }
  // empty email
  if (empty($_POST['email'])) {
    print('You should supply your email address.');
    exit();
  }
  // TODO validate email
  // TODO validate url

  // from here on it's safe to ignore the fact that it's user input
  $tag = $_POST['tag'];
  $author = $_POST['name'];
  $email = $_POST['email'];
  $comment = $_POST['comment'];
  $website = $_POST['website']; // TODO either call it website or site but now it's inconsistent

  try {
    $sql = $db->prepare('INSERT INTO comments (tag, author, comment, site) VALUES (:tag, :author, :comment, :site)');
    $sql->bindParam(':tag', $tag);
    $sql->bindParam(':author', $author);
    $sql->bindParam(':comment', $comment);
    $sql->bindParam(':site', $website);

    $sql->execute();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
  
  header('Location: ' . $directory . 'tag/' . $_POST['tag']);
?>
