<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// read configuration files
require_once("php/config.php");
$config = array_merge($config, parse_ini_file("config.ini"));

// all the pages
require_once("php/pages/about.php");
require_once("php/pages/acknowledgements.php");
require_once("php/pages/api.php");
require_once("php/pages/bibliography.php");
require_once("php/pages/browse.php");
require_once("php/pages/chapter.php");
require_once("php/pages/contribute.php");
require_once("php/pages/error.php");
require_once("php/pages/index.php");
require_once("php/pages/taghistory.php");
require_once("php/pages/markdown.php");
require_once("php/pages/missingtag.php");
require_once("php/pages/recentcomments.php");
require_once("php/pages/results.php");
require_once("php/pages/search.php");
require_once("php/pages/statistics.php");
require_once("php/pages/tagdeleted.php");
require_once("php/pages/taginvalid.php");
require_once("php/pages/taglookup.php");
require_once("php/pages/tags.php");
require_once("php/pages/tagview.php");
require_once("php/pages/todo.php");

// mapping the first chapter of each part to the title of the part
$parts = array(
  "Introduction"                    => "Preliminaries",
  "Schemes"                         => "Schemes",
  "Chow Homology and Chern Classes" => "Topics in Scheme Theory",
  "Algebraic Spaces"                => "Algebraic Spaces",
  "Chow Groups of Spaces"           => "Topics in Geometry",
  "Formal Deformation Theory"       => "Deformation Theory",
  "Algebraic Stacks"                => "Algebraic Stacks",
  "Moduli Stacks"                   => "Topics in Moduli Theory",
  "Examples"                        => "Miscellany");

// we try to construct the page object
try {
  // initialize the global database object
  try {
    $database = new PDO("sqlite:" . $config["database"]);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e) {
    print "Something went wrong with the database. If the problem persists, please contact us at <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>.";
    // if there is actually a persistent error: add output code here to check it
    exit();
  }

  if (empty($_GET["page"]))
    $page = "index";
  else
    $page = $_GET["page"];
  
  // all the possible page building scenarios
  switch($page) {
    case "about":
      $page = new AboutPage($database);
      break;
  
    case "acknowledgements":
      $page = new AcknowledgementsPage($database);
      break;

    case "api":
      $page = new APIPage($database);
      break;
  
    case "bibliography":
      if(!empty($_GET["key"])) {
        if (bibliographyItemExists($_GET["key"]))
          $page = new BibliographyItemPage($database, $_GET["key"]);
        else
          $page = new NotFoundPage("<p>The bibliography item with the key <var>" . htmlentities($_GET["key"]) . "</var> does not exist.");
      }
      else
        $page = new BibliographyPage($database);
      break;
  
    case "browse":
      $page = new BrowsePage($database);
      break;
  
    case "chapter":
      if (!is_numeric($_GET["chapter"]) or strstr($_GET["chapter"], ".") or intval($_GET["chapter"]) <= 0) {
        $page = new NotFoundPage("<p>The keys for a chapter should be (strictly) positive integers, but <var>" . htmlentities($_GET["chapter"]) . "</var> was provided.");
        break;
      }
  
      if (sectionExists($_GET["chapter"]))
        $page = new ChapterPage($database, intval($_GET["chapter"]));
      else
        $page = new NotFoundPage("<p>The chapter with the key <var>" . htmlentities($_GET["chapter"]) . "</var> does not exist.");
      break;
  
    case "contribute":
      $page = new ContributePage($database);
      break;
  
    case "index":
      $page = new IndexPage($database);
      break;

    case "history":
      if(!empty($_GET["tag"])) {
        $tag = strtoupper($_GET['tag']);

        if (!isValidTag($tag)) {
          $page = new InvalidTagPage($database, $tag);
        }
        elseif (tagExists($tag)) {
          if (tagIsActive($tag))
            $page = new HistoryPage($database, $tag);
          else
            $page = new TagDeletedPage($database, $tag);
        }
        else
          $page = new MissingTagPage($database, $tag);
      }
      else
        $page = new TagLookupPage($database);
      break;

    case "markdown":
      $page = new MarkdownPage($database);
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
        $page = new SearchPage($database);
      else {
        // TODO set options in new form
        $options = array();
        // based on SO1017599...
        $options["keywords"] = strtr(utf8_decode($_GET["keywords"]), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        if (isset($_GET["limit"]))
          $options["limit"] = $_GET["limit"];
        else
          $options["limit"] = "all";
        if (isset($_GET["exclude-duplicates"]))
          $options["exclude-duplicates"] = "on";
        $page = new SearchResultsPage($database, $options);
      }
      break;
  
    case "statistics":
      if(!empty($_GET["tag"])) {
        $tag = strtoupper($_GET['tag']);

        if (!isValidTag($tag)) {
          $page = new InvalidTagPage($database, $tag);
        }

        if (tagExists($tag)) {
          if (tagIsActive($tag))
            $page = new StatisticsPage($database, $tag);
          else
            $page = new TagDeletedPage($database, $tag);
        }
        else
          $page = new MissingTagPage($database, $tag);
      }
      else
        $page = new TagLookupPage($database);
      break;
  
    case "tag":
      if(!empty($_GET["tag"])) {
        $tag = strtoupper($_GET['tag']);
        if (!isValidTag($tag)) {
          $page = new InvalidTagPage($database, $tag);
        }
        else if (tagExists($tag)) {
          if (tagIsActive($tag))
            $page = new TagViewPage($database, $tag);
          else
            $page = new TagDeletedPage($database, $tag);
        }
        else
          $page = new MissingTagPage($database, $tag);
  
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

  // we request these now so that exceptions are thrown
  $title = $page->getTitle();
  $head = $page->getHead();
  $main = $page->getMain();
  $sidebar = $page->getSidebar();
}
catch(PDOException $e) {
  $page = new ErrorPage($e);

  // we request these now so that exceptions are thrown
  $title = $page->getTitle();
  $head = $page->getHead();
  $main = $page->getMain();
  $sidebar = $page->getSidebar();
}

?>
<!doctype html>
<html>
  <head>
    <title>Stacks Project<?php print $title; ?></title>
    <link rel='stylesheet' type='text/css' href='<?php print href("css/main.css"); ?>'>

    <link rel='icon' type='image/vnd.microsoft.icon' href='<?php print href("stacks.ico"); ?>'> 
    <link rel='shortcut icon' type='image/vnd.microsoft.icon' href='<?php print href("stacks.ico"); ?>'> 
    <meta charset='utf-8'>

    <?php print $head; ?>
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
      <li><a href='<?php print href("slogans"); ?>'>add slogans</a>
    </ul>
    <br style='clear: both;'>

    <div id='main'>
      <?php print $main; ?>
    </div>

    <div id='sidebar'>
      <?php print $sidebar; ?>
    </div>

    <br style='clear: both;'>

    <p id='backlink'>Back to the <a href='<?php print href(''); ?>'>main page</a>.
  </body>
</html>
