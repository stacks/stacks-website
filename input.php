<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = parse_ini_file("config.ini");

// no specific tag was requested: we get one from the server and forward the user to the specific slogan page
// TODO make sure that slogan input pages are not indexed by search engines
if (!isset($_GET["tag"])) {
  $tag = file_get_contents($config["site"] . "/data/slogan/random");
  header("Location: input.php?tag=" . $tag);
}

function getSlogans($tag) {
  return array(); // TODO implement this
}

function printError($message) {
  print "<div id='error'>";
  print "<p>Something went wrong:";
  print "<p>";
  print $message;
  print <<<EOD
<form action="submit.php" method="post">
  <input type="submit" name="skip" id="skip" value="get new tag">
  <br style="clear:both">
</form>
EOD;
  print "</div>";
}

function printForm($tag) {
  print <<<EOD
<form action="submit.php" method="post">
  <p>A slogan should be a human-readable summary of the tag's statement, in a single sentence, without using symbols.</p>

  <label for="slogan">Slogan<sup>*</sup>:</label>
  <textarea name="slogan" id="slogan-input" rows="2" autofocus></textarea>
  <br style="clear:both">

  <hr>

  <label for="name">Name<sup>*</sup>:</label>
  <input type="text" id="name" name="name" class="stored" size="30">
  <br style="clear:both">

  <label for="email">E-mail<sup>*</sup>:</label>
  <input type="email" id="email" name="email" class="stored" size="30">
  <br style="clear:both">

  <hr>

  <p>Prove you are human: <em>fill in the name of the current tag</em>. In case this were tag&nbsp;<var>0321</var> you just have to write&nbsp;<var>0321</var>.
EOD;
  print " This is tag&nbsp;<var>" . $tag . "</var>.</p>";
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

function printSlogans($slogans) {
  print "<h2 id='slogans-title'>Existing slogans for this tag</h2>";
  print "<ol id='slogans'>";

  foreach ($slogans as $slogan)
    print "<li><span class='slogan'>" . $slogan["slogan"] . "</span> <cite>" . $slogan["author"] . "</cite>"; // TODO fix escaping here (!!)

  print "</ol>";
}

function printStatement($tag) {
  global $config;

  // request the HTML for this tag
  $statement = file_get_contents($config["site"] . "/data/tag/" . $tag . "/content/statement");
  
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
  <link rel="stylesheet" type="text/css" href="<?php print $config["site"]; ?>/css/tag.css">
  <link rel="stylesheet" type="text/css" href="style.css">

  <script type='text/x-mathjax-config'>
    MathJax.Hub.Config({
      extensions: ['tex2jax.js'],
      tex2jax: {inlineMath: [['$', '$']]},
      TeX: {extensions: ['http://sonoisa.github.io/xyjax_ext/xypic.js', 'AMSmath.js', 'AMSsymbols.js'], TagSide: 'left'},
      'HTML-CSS': { scale: 85 }
    });
  </script>
  <script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=default"></script>
  <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.0.min.js"></script>

</head>

<body>

<h1><a href="#">The Stacks project sloganerator</a></h1>

<?php

// TODO sanity checks
$tag = $_GET["tag"];
// TODO check existence, if not error

$meta = json_decode(file_get_contents($config["site"] . "/data/tag/" . $tag . "/meta"));
if (in_array($meta->type, array("lemma", "proposition", "remark", "remarks", "theorem"))) {
  print "<p>You can suggest a slogan for <a href='" . $config["site"] . "/tag/" . $tag . "'>tag <var>" . $tag . "</var></a>, located in<br>";

  $id = explode(".", $meta->book_id);
  print "&nbsp&nbsp;Chapter " . $id[0] . ": " . $meta->chapter_name . "<br>";
  print "&nbsp&nbsp;Section " . $id[1] . ": " . $meta->section_name;
  printStatement($tag);
  printForm($tag);

  $slogans = getSlogans($tag);
  if (!empty($slogans))
    printSlogans($slogans);
}
else {
  $message = "The tag that was requested (<var>" . $tag . "</var>) is of type <var>" . $meta->type . "</var>, but it is impossible to write slogans for tags of this type.";
  printError($message);
}
?>

<script type="text/javascript" src="slogan.js"></script>
</body>
</html>
