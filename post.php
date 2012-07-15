<?php
  include('config.php');
  include('functions.php');

  try {
    $db = new PDO(get_database_location());
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  // if this triggers the user is messing with the POST request
  if (!is_valid_tag($_POST['tag'])) {
    print('The tag your browser supplied in the request is not in a valid format.');
    exit();
  }

  if ($_POST['tag'] !== $_POST['check']) {
    print('You did not pass the captcha. Please go back and fill in the correct tag to prove you are not a computer.');
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
      $site = 'http://' . $_POST['site'];
    }
  }

  // nonempty site, but the format is wrong
  if (!empty($site) and !filter_var($_POST['site'], FILTER_VALIDATE_URL)) {
    print('You must supply a correctly formatted site. Your current input is ' . $_POST['site']);
  }

  // from here on it's safe to ignore the fact that it's user input
  $tag = $_POST['tag'];
  $author = $_POST['name'];
  $email = $_POST['email'];
  $comment = $_POST['comment'];
  // for some reason Firefox is inserting &nbsp;'s in the input when you have two consecutive spaces, we don't like that
  $comment = str_replace('&nbsp;', ' ', $comment);
  // escape all underscores for correct math parsing
  $comment = str_replace("_", "\\_", $comment);
  // $site is already handled

  try {
    $sql = $db->prepare('INSERT INTO comments (tag, author, comment, site) VALUES (:tag, :author, :comment, :site)');
    $sql->bindParam(':tag', $tag);
    $sql->bindParam(':author', $author);
    $sql->bindParam(':comment', $comment);
    $sql->bindParam(':site', $site);

    if(!$sql->execute()) {
      print("Something went wrong with your comment.\n");
      print_r($sql->errorInfo());
      exit();
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  header('Location: ' . full_url('tag/' . $_POST['tag']));
?>
