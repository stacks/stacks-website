<?php
  include("general.php");
  include('tags.php');

  // read configuration file
  $config = parse_ini_file("../config.ini");
  
  // initialize the global database object
  try {
    $database = new PDO("sqlite:" . $config["database"]);
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  // if this triggers the user is messing with the POST request
  if (!isValidTag($_POST['tag'])) {
    print('The tag your browser supplied in the request is not in a valid format.');
    exit();
  }

  if ($_POST['tag'] !== $_POST['check']) {
    print('You did not pass the captcha. Please go back and fill in the correct tag to prove you are not a computer.');
    exit();
  }

  // the tag is not present in the database, when we start handling removed tags this will have to change
  if (!tagExists($_POST['tag'])) {
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
  $site = $_POST['site'];
  if (!empty($site)) {
    // incorrect url, probably missing http:// we prepend it and try again
    if (!filter_var($site, FILTER_VALIDATE_URL)) {
      $site = 'http://' . $site;
    // nonempty site, but the format is wrong
      if (!filter_var($site, FILTER_VALIDATE_URL)) {
        print('You supplied a site but the format is wrong. Your current input is ' . $_POST['site']);
        exit();
      }
    }
  }

  // from here on it's safe to ignore the fact that it's user input
  $tag = $_POST['tag'];
  $author = $_POST['name'];
  $email = $_POST['email'];
  $comment = $_POST['comment'];
  // for some reason Firefox is inserting &nbsp;'s in the input when you have two consecutive spaces, we don't like that
  $comment = str_replace('&nbsp;', ' ', $comment);
  // $site is already handled

  try {
    $sql = $database->prepare('INSERT INTO comments (tag, author, comment, site, email) VALUES (:tag, :author, :comment, :site, :email)');
    $sql->bindParam(':tag', $tag);
    $sql->bindParam(':author', $author);
    $sql->bindParam(':comment', htmlspecialchars_decode($comment));
    $sql->bindParam(':site', $site);
    $sql->bindParam(':email', $email);

    if(!$sql->execute()) {
      print("Something went wrong with your comment.\n");
      print_r($sql->errorInfo());
      exit();
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  header('Location: ' . href('tag/' . $_POST['tag']) . '#comment-' . $database->lastInsertId());
?>
