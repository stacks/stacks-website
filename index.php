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

require_once("php/pages/about.php");
require_once("php/pages/acknowledgements.php");
require_once("php/pages/bibliography.php");
require_once("php/pages/browse.php");
require_once("php/pages/chapter.php");
require_once("php/pages/contribute.php");
require_once("php/pages/index.php");
require_once("php/pages/missingtag.php");
require_once("php/pages/recentcomments.php");
require_once("php/pages/results.php");
require_once("php/pages/search.php");
require_once("php/pages/statistics.php");
require_once("php/pages/tagdeleted.php");
require_once("php/pages/taglookup.php");
require_once("php/pages/tags.php");
require_once("php/pages/tagview.php");
require_once("php/pages/todo.php");

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
  case "acknowledgements":
    $page = new AcknowledgementsPage($database);
    break;
  case "bibliography":
    // TODO some checking of this value
    if(!empty($_GET["key"]))
      $page = new BibliographyItemPage($database, $_GET["key"]);
    else
      $page = new BibliographyPage($database);
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
  case "contribute":
    $page = new ContributePage($database);
    break;
  case "index":
    $page = new IndexPage($database);
    break;
  case "recent-comments":
    if (empty($_GET["number"]))
      $number = 1;
    else
      $number = $_GET["number"];

    $page = new RecentCommentsPage($database, $number);
    break;
  case "search":
    if (!isset($_GET["keywords"]))
      $page = new SearchPage($database); // this page doesn't need the database? maybe change the structure of the Page object?
    else {
      // TODO some preprocessing / checking / pagination
      // TODO set options in new form
      $options = array();
      $options["keywords"] = $_GET["keywords"];
      $options["limit"] = "all";
      $page = new SearchResultsPage($database, $options);
    }
    break;
  case "statistics":
    // TODO some checking of this value
    if(!empty($_GET["tag"])) {
      if (tagExists($_GET["tag"])) {
        if (tagIsActive($_GET["tag"]))
          $page = new StatisticsPage($database, $_GET["tag"]);
        else
          $page = new TagDeletedPage($database, $_GET["tag"]); // TODO something more reasonable
      }
      else
        $page = new MissingTagPage($database, $_GET["tag"]); // TODO something more reasonable
    }
    else
      $page = new TagLookupPage($database);
    break;
  case "tag":
    // TODO some checking of this value
    if(!empty($_GET["tag"])) {
      if (tagExists($_GET["tag"])) {
        if (tagIsActive($_GET["tag"]))
          $page = new TagViewPage($database, $_GET["tag"]);
        else
          $page = new TagDeletedPage($database, $_GET["tag"]);
      }
      else
        $page = new MissingTagPage($database, $_GET["tag"]);

    }
    else
      $page = new TagLookupPage($database);
    break;
  case "tags":
    $page = new TagsPage($database);
    break;
  case "todo":
    $page = new TodoPage($database);
    break;
}

?>
<!doctype html>
<html>
  <head>
    <title>Stacks Project<?php print $page->getTitle(); ?></title>
    <link rel='stylesheet' type='text/css' href='<?php print href("css/main.css"); ?>'>

    <link rel='icon' type='image/vnd.microsoft.icon' href='<?php print href("stacks.ico"); ?>'> 
    <link rel='shortcut icon' type='image/vnd.microsoft.icon' href='<?php print href("stacks.ico"); ?>'> 
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
      <li><a href='<?php print href("search"); ?>'>search</a>
      <li><a href='<?php print href("bibliography"); ?>'>bibliography</a>
      <li><a href='<?php print href("recent-comments"); ?>'>recent comments</a>
      <li><a href='<?php print $config["blog"]; ?>'>blog</a>
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
