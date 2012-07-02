<?php
  error_reporting(E_ALL);
  
  include('config.php');
  include('php-markdown/markdown.php');

  try {
    $db = new PDO('sqlite:stacks.sqlite');
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  function print_comment_item($tag, $id, $author, $date, $comment) {
    global $domain;

?>
    <item>
      <title>Comment on tag <?php print($tag); ?></title>
      <link><?php print($domain . full_url('tag/' . $tag . '#comment-' . $id)); ?></link>
      <description>A new comment by <?php print(htmlentities($author)); ?> on tag <?php print($tag); ?>.</description>
      <content:encoded><![CDATA[<?php print(Markdown($comment)); ?>]]></content:encoded>
      <dc:creator><?php print($author); ?></dc:creator>
      <pubDate><?php print(date_format(date_create($date, timezone_open('GMT')), DATE_RFC2822));?></pubDate>
    </item>
<?php
  }

  function print_comments_feed($limit) {
    global $db;
    
    try {
      $sql = $db->prepare('SELECT id, tag, author, date, comment FROM comments ORDER BY date DESC LIMIT 0, :limit');
      $sql->bindParam(':limit', $limit);

      if ($sql->execute()) {
        while ($row = $sql->fetch()) {
          print_comment_item($row['tag'], $row['id'], $row['author'], $row['date'], $row['comment']);
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title>Stacks project -- Comments</title>
    <link><?php print($domain . full_url('comments-feed.rss')); ?></link>
    <description>Stacks project, see http://math.columbia.edu/algebraic_geometry/stacks-git</description>

    <language>en</language>
    <managingEditor>stacks.project@gmail.com (Stacks Project)</managingEditor>
    <webMaster>pieterbelmans@gmail.com (Pieter Belmans)</webMaster>
    <image>
      <url><?php print($domain . full_url('stacks.ico')); ?></url>
      <title>Stacks project -- Comments</title>
      <link><?php print($domain . full_url('comments-feed.rss')); ?></link>
    </image>

<?php
  print_comments_feed(10);
?>
  </channel>
</rss>
