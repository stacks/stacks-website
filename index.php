<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// read configuration file
$config = parse_ini_file("config.ini");

// initialize the global database object
try {
  $database = new PDO("sqlite:" . $config["database"]);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

include("php/pages/about.php");
include("php/pages/browse.php");
include("php/pages/chapter.php");
include("php/pages/index.php");
include("php/pages/taglookup.php");
include("php/pages/tags.php");
include("php/pages/tagview.php");

// TODO some error code
// TODO "index" is default, no, should be an error message (but "index" == "")

if (empty($_GET["page"]))
  $page = "index";
else
  $page = $_GET["page"];

switch($page) {
  case "about":
    $page = new AboutPage($database);
    break;
  case "browse":
    $page = new BrowsePage($database);
    break;
  case "chapter":
    // TODO some checking of this value
    if (section_exists($_GET["chapter"])) {
      $page = new ChapterPage($database, $_GET["chapter"]);
    }
    else {
      $page = new AboutPage($database); // TODO an appropriate error page
    }
    break;
  case "index":
    $page = new IndexPage($database);
    break;
  case "tag":
    if(!empty($_GET["tag"]))
      $page = new TagViewPage($database, $_GET["tag"]);
    else
      $page = new TagLookupPage($database);
    break;
  case "tags":
    $page = new TagsPage($database);
    break;
}

?>
<!doctype html>
<html>
  <head>
    <title>Stacks Project<?php print $page->getTitle(); ?></title>
    <link rel='stylesheet' type='text/css' href='<?php print href("css/main.css"); ?>'>
    <link rel='stylesheet' type='text/css' href='css/tag.css'>

    <link rel='icon' type='image/vnd.microsoft.icon' href='stacks.ico'> 
    <link rel='shortcut icon' type='image/vnd.microsoft.icon' href='stacks.ico'> 
    <meta charset='utf-8'>

    <?php print $page->getHead(); ?>
  </head>

  <body>
  <h1><a href='<?php print href(''); ?>'>The Stacks Project</a></h1>

    <ul id='menu'>
      <li><a href='<?php print href(""); ?>'>home</a>
      <li><a href='<?php print href("about"); ?>'>about</a>
      <li><a href='<?php print href("tags"); ?>'>tags explained</a>
      <li><a href='<?php print href("tag"); ?>'>tag lookup</a>
      <li><a href='<?php print href("browse"); ?>'>browse</a>
      <li><a href='#'>search</a>
      <li><a href='#'>bibliography</a>
      <li><a href='#'>recent comments</a>
      <li><a href='http://math.columbia.edu/~dejong/wordpress/'>blog</a>
    </ul>
    <br style='clear: both;'>

    <div id='main'>
      <?php print $page->getMain(); ?>
    </div>

    <div id='sidebar'>
      <?php print $page->getSidebar(); ?>
    </div>

    <br style='clear: both;'>

    <p id='backlink'>Back to the <a href='/'>main page</a>.
  </body>
</html>
