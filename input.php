<!doctype html>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = array("site" => "http://localhost:10000");
?>
<html lang="en">
<head>
  <meta charset="utf-8">

  <title>The Stacks project sloganerator: design mockup</title>
  <link rel="stylesheet" type="text/css" href="http://stacks.math.columbia.edu/css/tag.css">
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

<blockquote id="statement" class="rendered">
<?php
// request a tag for which we want people to write a slogan
$tag = file_get_contents($config["site"] . "/data/slogan/random");
// TODO some checks

// request the HTML for this tag
$statement = file_get_contents($config["site"] . "/data/tag/" . $tag . "/content/statement");

print $statement;
?>
</blockquote>

<form action="submit.php" method="post">
  <p>A slogan should be a human-readable summary of the tag's statement, in a single sentence, without using symbols.</p>

  <label for="slogan">Slogan<sup>*</sup>:</label>
  <textarea rows="2" autofocus required></textarea>
  <br style="clear:both">

  <hr>

  <label for="name">Name<sup>*</sup>:</label>
  <input type="text" id="name" name="name" class="stored" size="30" required>
  <br style="clear:both">

  <label for="email">E-mail<sup>*</sup>:</label>
  <input type="email" id="email" name="email" class="stored" size="30" required>
  <br style="clear:both">

  <hr>

  <p>Prove you are human: <em>fill in the name of the current tag</em>. In case this were tag&nbsp;<var>0321</var> you just have to write&nbsp;<var>0321</var>. This is tag&nbsp;<var>01ZA</var>.</p>
  <label for="tag">Tag<sup>*</sup>:</label>
  <input type="text" id="tag" name="tag" size="4" maxlength="4" required>
  <br style="clear:both">

  <hr>

  <input type="submit" name="skip" id="skip" value="get new tag">
  <input type="submit" name="skip" id="submit" value="submit this slogan">
  <br style="clear:both">
</form>

<h2 id="slogans-title">Existing slogans for this tag</h2>
<ol id="slogans">
  <li><span class="slogan">Absolute noetherian approximation</span> <cite>Pieter Belmans</cite>
  <li><span class="slogan">Relative artinian approximation</span> <cite>Pieter Belmans</cite>
</ol>

<script type="text/javascript" src="slogan.js"></script>
</body>
</html>
