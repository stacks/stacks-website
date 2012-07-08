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

  function print_comment($comment) {
    $tag_information = get_tag($comment['tag']);
    $date = date_create($comment['date'], timezone_open('GMT'));
?>
      <li value="<?php print($comment['id']); ?>">On <?php print(date_format($date, 'F j')); ?> <?php (empty($comment['site'])) ? print(htmlspecialchars($comment['author'])) : print("<a href='" . htmlspecialchars($comment['site']) . "'>" . htmlspecialchars($comment['author']) . "</a>"); ?> left <a href="<?php print(full_url('tag/' . $comment['tag'] . "#comment-" . $comment['id'])) ?>">a comment </a> on <a href="<?php print(full_url('tag/' . $comment['tag'])); ?>"><var title="<?php print($tag_information['label']); ?>">tag <?php print($comment['tag']); ?></var></a>
        <blockquote>
          <?php print(htmlentities(substr($comment['comment'], 0, 100)) . '...'); ?>
        </blockquote>
<?php
  }

  function print_recent_comments($limit) {
    global $db;

    print("    <ol>\n");
    
    try {
      $sql = $db->prepare('SELECT id, tag, author, site, date, comment FROM comments ORDER BY date DESC LIMIT 0, :limit');
      $sql->bindParam(':limit', $limit);

      if ($sql->execute()) {
        while ($row = $sql->fetch()) {
          print_comment($row);
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    print("    </ol>\n");
  }
?>
<html>
  <head>
    <title>Stacks Project -- Recent comments</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">

    <style type="text/css">
      blockquote {
        margin: .4em 0 0 3em;
      }
    </style>
    
    <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php print($domain . full_url('recent-comments.rss')); ?>">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>

    <p>There is also an <a href="<?php print(full_url('recent-comments.rss')); ?>"><abbr title="Really Simple Syndication">RSS</abbr> feed <img src="<?php print(full_url('rss-icon.png')); ?>"></a> if you wish to follow the recent comments from your newsreader.</p>

    <h2>Recent comments</h2>
<?php
  print_recent_comments(20);
?>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
