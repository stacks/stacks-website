<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once("../php/general.php");
include_once("../php/tags.php");

session_start();

$config = parse_ini_file("../config.ini");

try {
  $database = new PDO("sqlite:../" . $config["database"]);
  $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
  print "Something went wrong with the database. If the problem persists, please contact us at <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>.";
  // if there is actually a persistent error: add output code here to check it
  exit();
}

// no specific tag was requested: we get one from the server and forward the user to the specific slogan page
if (!isset($_GET["tag"])) {
  $tag = file_get_contents("http://" . $_SERVER["HTTP_HOST"] . href("/data/slogan/random"));

  header("Location: " . href("slogans/" . $tag));
}

function getSlogans($tag) {
  global $database;

  $sql = $database->prepare("SELECT slogan, author FROM slogans WHERE tag = :tag ORDER BY id DESC");
  $sql->bindParam(":tag", $tag);

  if ($sql->execute())
    return $sql->fetchAll();
}

function printError($message) {
  print "<div id='error'>";
  print "<p>Something went wrong:";
  print "<p>";
  print $message;
  print <<<EOD
<form action="/sloganerator/submit.php" method="post">
  <input type="submit" name="skip" id="skip" value="get new tag">
  <br style="clear:both">
</form>
EOD;
  print "</div>";
}

function printForm($tag) {
  print <<<EOD
<form action="/sloganerator/submit.php" method="post">
  <p>A slogan should be a human-readable summary of the tag's statement, in a single sentence, without using symbols.</p>

  <label for="slogan">Slogan<sup>*</sup>:</label>
  <textarea name="slogan" id="slogan-input" rows="2" autofocus></textarea>
  <br style="clear:both">

  <hr>

  <label for="name">Name<sup>*</sup>:</label>
  <input type="text" id="name" name="name" class="stored" size="30">
  <br style="clear:both">

  <label for="mail">E-mail<sup>*</sup>:</label>
  <input type="email" id="mail" name="mail" class="stored" size="30">
  <br style="clear:both">

  <label for="site">Site:</label>
  <input type="url" id="site" name="site" class="stored" size="30">
  <br style="clear:both">

  <hr>

  <p>Prove you are human: <em>fill in the name of the current tag</em>. In case this were tag&nbsp;<code>0321</code> you just have to write&nbsp;<code>0321</code>.
EOD;
  print " This is tag&nbsp;<code>" . $tag . "</code>.</p>";
  print "<input type='hidden' name='tag' value='" . $tag . "'>";
  print <<<EOD
  <label for="tag">Tag<sup>*</sup>:</label>
  <input type="text" id="check" name="check" size="4" maxlength="4">
  <br style="clear:both">

  <hr>

  <input type="submit" name="submit" id="submit" value="submit this slogan">
  <input type="submit" name="skip" id="skip" value="get new tag">
  <br style="clear:both">
</form>
EOD;
}

function printSlogans($slogans, $existing) {
  print "<h2 id='slogans-title'>Existing slogans for this tag</h2>";
  print "<div id='existing'>";
  if ($existing != "") {
    print "<p>There is already a slogan in use for this tag in the Stacks project:";
    print "<blockquote>" . $existing;
  }

  print "<ol id='slogans'>";

  foreach ($slogans as $slogan)
    print "<li><span class='slogan'>" . htmlentities($slogan["slogan"]) . "</span> <cite>" . htmlentities($slogan["author"]) . "</cite>";

  print "</ol>";
  print "</div>";
}

function printStatement($tag) {
  global $config;

  // request the HTML for this tag
  $statement = file_get_contents("http://" . $_SERVER["HTTP_HOST"] . href("data/tag/" . $tag . "/content/statement"));

  print "<blockquote id='statement' class='rendered'>";
  print $statement;
  print "</blockquote>";
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">

  <title>The Stacks project sloganerator: design mockup</title>
  <link rel="stylesheet" type="text/css" href="<?php print href("css/tag.css"); ?>">
  <link rel="stylesheet" type="text/css" href="<?php print href("slogans/style.css"); ?>">

  <script type='text/x-mathjax-config'>
    MathJax.Hub.Config({
      extensions: ['tex2jax.js'],
      tex2jax: {inlineMath: [['$', '$']]},
      TeX: {extensions: ['<?php print href("js/XyJax/extensions/TeX/xypic.js"); ?>', 'AMSmath.js', 'AMSsymbols.js'], TagSide: 'left'},
      'HTML-CSS': { scale: 85 }
    });
  </script>
  <script type="text/javascript" src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=default"></script>
  <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
</head>

<body>

<h1><a href="<?php print href(""); ?>">The Stacks project sloganerator</a></h1>

<?php

$tag = isset($_GET["tag"]) ? $_GET["tag"] : '';

if (!isValidTag($tag)) {
  $message = "The tag that was requested (<code>" . htmlentities($tag) . "</code>) is not a valid tag. You can request a new tag.";
  printError($message);
}
elseif (!tagExists($tag)) {
  $message = "The tag that was requested (<code>" . $tag . "</code>) does not exist in the Stacks project. You can request a new tag.";
  printError($message);
}
else {
  $meta = json_decode(file_get_contents("http://" . $_SERVER["HTTP_HOST"] . href("data/tag/" . $tag . "/meta")));
  if (in_array($meta->type, array("lemma", "proposition", "remark", "remarks", "theorem"))) {
    print "<p>You can suggest a slogan for <a href='" . href("tag/" . $tag) . "'>tag <code>" . $tag . "</code></a> (label: <code style='font-size: .9em'>" . $meta->label . "</code>), located in<br>";

    $id = explode(".", $meta->book_id);
    print "&nbsp&nbsp;Chapter " . $id[0] . ": " . parseAccents($meta->chapter_name) . "<br>";
    print "&nbsp&nbsp;Section " . $id[1] . ": " . parseAccents($meta->section_name);
    printStatement($tag);
    printForm($tag);

    $slogans = getSlogans($tag);
    if (!empty($slogans) or $meta->slogan != "")
      printSlogans($slogans, $meta->slogan);
  }
  else {
    $message = "The tag that was requested (<code>" . $tag . "</code>) is of type <code>" . $meta->type . "</code>, but it is impossible to write slogans for tags of this type.";
    printError($message);
  }
}
?>

<script type="text/javascript" src="slogan.js"></script>
<?php

if (isset($_SESSION["tag"]) && isset($_GET["tag"])) {
  print "<script type='text/javascript'>";
  print "$(function() { success('" . $_SESSION["tag"] . "'); });";
  print "</script>";

  unset($_SESSION["tag"]);
}
?>

<p id="footer">All contributions are licensed under the <a href='https://github.com/stacks/stacks-project/blob/master/COPYING'>GNU Free Documentation License</a>.</p>
</body>
</html>
