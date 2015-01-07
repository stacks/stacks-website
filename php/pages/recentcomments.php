<?php

require_once("php/page.php");
require_once("php/general.php");

$config["comments per page"] = 20;
$config["comments cutoff"] = 100;

// move these inside the class?
function countComments($db) {
  $sql = $db->prepare("SELECT COUNT(*) FROM comments");

  if ($sql->execute())
    return $sql->fetchColumn();

  return 0;
}

function getComments($db, $start, $number) {
  $sql = $db->prepare("SELECT id, tag, author, site, date, comment FROM comments ORDER BY date DESC LIMIT :start, :offset");
  $sql->bindParam(":start", $start);
  $sql->bindParam(":offset", $number);

  if ($sql->execute())
    return $sql->fetchAll();
}

function printComment($comment) {
  global $config;
  $output = "";

  $tag = getTag($comment['tag']);
  $date = date_create($comment['date'], timezone_open('GMT'));
  $output .= "<li>On " . date_format($date, 'F j') . " ";
  if (empty($comment['site']))
    $output .= htmlspecialchars($comment['author']);
  else
    $output .= "<a href='" . htmlspecialchars($comment['site']) . "'>" . htmlspecialchars($comment['author']) . "</a>";
  $output .= " left <a href='" . href("tag/" . $comment["tag"] . "#comment-" . $comment['id']) . "'>comment " . $comment['id'] . "</a>";
  $output .= " on <a href='" . href('tag/' . $comment['tag']) . "'>tag <var title='" . $tag['label'] . "'>" . $comment['tag'] . "</var></a>";

  $output .= "<blockquote>";
  $output .= htmlentities(substr($comment['comment'], 0, $config["comments cutoff"])) . (strlen($comment['comment']) > $config["comments cutoff"] ? '...' : '');
  $output .= "</blockquote>";

  return $output;
}

class RecentCommentsPage extends Page {
  public $page;

  public function __construct($database, $page) {
    $this->db = $database;
    $this->page = intval($page);

    $this->commentsCount = countComments($database);
  }

  public function getHead() {
    $output = "";

    $output .= "<link rel='stylesheet' type='text/css' href='" . href('css/recent-comments.css') . "'>";
    $output .= "<link rel='alternate' type='application/rss+xml' title='RSS' href=" . href('recent-comments.xml') . ">"; // TODO check should this be absolute?

    return $output;
  }

  public function getMain() {
    global $config;
    $output = "";

    $output .= "<h2>Recent comments</h2>";
    $output .= "<p>There are currently " . $this->commentsCount . " comments. ";
    $output .= "You are now displaying comments " . ($this->page - 1) * $config["comments per page"] . " to " . min($this->page * $config["comments per page"], $this->commentsCount) . " in reverse chronological order.</p>";

    $comments = getComments($this->db, ($this->page - 1) * $config["comments per page"], $config["comments per page"]);
    $output .= "<ul>";
    foreach ($comments as $comment)
      $output .= printComment($comment);
    $output .= "</ul>";

    return $output;
  }
  public function getSidebar() {
    global $config;
    $output = "";

    $output .= "<h2>Navigation</h2>";
    $output .= "<p class='navigation'>";
    if ($this->page >= 2) // TODO also check that this makes sense
      $output .= "<span class='left'><a href='" . href("recent-comments/" . ($this->page - 1)) . "'>&lt;&lt; previous page</a></span>";

    if ($this->page < ceil($this->commentsCount / $config["comments per page"]))
      $output .= "<span class='right'><a href='" . href("recent-comments/" . ($this->page + 1)) . "'>next page &gt;&gt;</a></span>";
    else // we need this for the layout
      $output .= "<span class='right'>&nbsp;</span>";

    $output .= "</p>";

    $output .= "<h2>Recent comments feed</h2>";
    $output .= "<p>There is also an <a class='rss' href='" . href("recent-comments.rss") . "'><abbr title='Really Simple Syndication'>RSS</abbr> feed</a> if you wish to follow the recent comments from your newsreader. ";
    
    return $output;
  }
  public function getTitle() {
    return " &mdash; Recent comments";
  }
}

?>


