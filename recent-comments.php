<!doctype html>
<?php
  include('config.php');
  include('functions.php');
  include('php-markdown-extra-math/markdown.php');

  try {
    $db = new PDO(get_database_location());
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  function count_comments() {
    global $db;

    try {
      $sql = $db->prepare('SELECT COUNT(*) FROM comments');

      if ($sql->execute())
        return $sql->fetchColumn();
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    return 0;
  }

  function print_comment($comment) {
    $tag_information = get_tag($comment['tag']);
    $date = date_create($comment['date'], timezone_open('GMT'));
?>
      <li>On <?php print(date_format($date, 'F j')); ?> <?php (empty($comment['site'])) ? print(htmlspecialchars($comment['author'])) : print("<a href='" . htmlspecialchars($comment['site']) . "'>" . htmlspecialchars($comment['author']) . "</a>"); ?> left <a href="<?php print(full_url('tag/' . $comment['tag'] . "#comment-" . $comment['id'])) ?>">comment <?php print($comment['id']); ?></a> on <a href="<?php print(full_url('tag/' . $comment['tag'])); ?>">tag <var title="<?php print($tag_information['label']); ?>"><?php print($comment['tag']); ?></var></a>
        <blockquote>
<?php
  $cutoff = 100;
  print(htmlentities(substr($comment['comment'], 0, $cutoff)) . (strlen($comment['comment']) > $cutoff ? '...' : ''));
?>
        </blockquote>
<?php
  }

  function print_recent_comments($start, $number) {
    global $db;

    print("    <ul>\n");
    
    try {
      $sql = $db->prepare('SELECT id, tag, author, site, date, comment FROM comments ORDER BY date DESC LIMIT :start, :stop');
      $sql->bindParam(':start', $start);
      $stop = $start + $number;
      $sql->bindParam(':stop', $stop);

      if ($sql->execute()) {
        while ($row = $sql->fetch()) {
          print_comment($row);
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    print("    </ul>\n");
  }
?>
<html>
  <head>
    <title>Stacks Project -- Recent comments</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">

    <style type="text/css">
      blockquote {
        margin: .4em 0 1em 3em;
      }
    </style>
    
    <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php print($domain . full_url('recent-comments.xml')); ?>">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>Recent comments</h2>
    <p>There is also an <a href="<?php print(full_url('recent-comments.xml')); ?>"><abbr title="Really Simple Syndication">RSS</abbr> feed <img src="<?php print(full_url('rss-icon.png')); ?>"></a> if you wish to follow the recent comments from your newsreader.

<?php
  $comments_per_page = 20;
  $comments_count = count_comments();
  print("There are currently " . $comments_count . " comments. ");

  // no page number supplied, this means we're on page 1 (= most recent comments)
  if (!isset($_GET['page']))
    $page = 1;
  else
    $page = $_GET['page'];

  if (is_numeric($page)) {
    $page = intval($page);
    print("Now you are displaying comments " . ($page - 1) * $comments_per_page . " to " . min($page * $comments_per_page, $comments_count) . " in reverse chronological order.</p>");
    // there is a previous page
    if ($page >= 2)
      print("<p id='navigate-back'><a href='" . full_url('recent-comments/' . ($page - 1)) . "'>&lt;&lt; previous page</a></p>");

    if ($page < ceil($comments_count / $comments_per_page))
      print("<p id='navigate-forward'><a href='" . full_url('recent-comments/' . ($page + 1)) . "'>next page &gt;&gt;</a></p>");
    else
      print("<br style='clear: both; height: 0;'>");

    print_recent_comments(($page - 1) * $comments_per_page, $comments_per_page);
  }
  // bogus input
  else {
    print("The page you requested isn't in the right format, it should be an integer in the range from 1 to " . ceil($comments_count / $comments_per_page) . " given the current amount of comments.</p>");
  }
?>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
