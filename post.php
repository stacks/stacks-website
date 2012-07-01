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
    print('You must supply your name.');
    exit();
  }
  // empty email
  if (empty($_POST['email'])) {
    print('You must supply your email address. Remark that it will not be posted.');
    exit();
  }
  // nonempty email, but the format is wrong
  if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    print('You must supply a correctly formatted email address. Your current input is ' . $_POST['email']);
    exit();
  }
  // first a little cleanup of the site field
  if (!empty($_POST['site'])) {
    // incorrect url, missing http: we prepend it and try again
    if (!filter_var($_POST['site'], FILTER_VALIDATE_URL) and strpos('http', $_POST['site']) !== 0) {
      $_POST['site'] = 'http://' . $_POST['site'];
    }
  }
  // nonempty site, but the format is wrong
  if (!empty($_POST['site']) and !filter_var($_POST['site'], FILTER_VALIDATE_URL)) {
    print('You must supply a correctly formatted site. Your current input is ' . $_POST['site']);
  }

  // from here on it's safe to ignore the fact that it's user input
  $tag = $_POST['tag'];
  $author = $_POST['name'];
  $email = $_POST['email'];
  $comment = $_POST['comment'];
  $site = $_POST['site'];

  try {
    $sql = $db->prepare('INSERT INTO comments (tag, author, comment, site) VALUES (:tag, :author, :comment, :site)');
    $sql->bindParam(':tag', $tag);
    $sql->bindParam(':author', $author);
    $sql->bindParam(':comment', $comment);
    $sql->bindParam(':site', $site);

    $sql->execute();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
  
  header('Location: ' . full_url('tag/' . $_POST['tag']));
?>
