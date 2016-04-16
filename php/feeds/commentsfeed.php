<?php

require_once("../config.php");
$config = array_merge($config, parse_ini_file("../../config.ini"));

require_once("../general.php");

require_once('../markdown/markdown.php');

$domain = 'http://stacks.math.columbia.edu';

// initialize the global database object
try {
  $database = new PDO("sqlite:../../" . $config["database"]);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

function print_comment_item($tag, $id, $author, $date, $comment) {
  global $domain;
  $output = "";
  $output .= "  <item>\n";
  $output .= "    <title>#";
  $output .= $id;
  $output .= " on tag ";
  $output .= $tag;
  $output .= " by ";
  $output .= htmlentities($author);
  $output .= "</title>\n";
  $output .= "    <link>";
  $output .= $domain . href('tag/' . $tag . '#comment-' . $id);
  $output .= "</link>\n";
  $output .= "    <description>A new comment by ";
  $output .= htmlentities($author);
  $output .= " on tag ";
  $output .= $tag;
  $output .= ".";
  $output .= "</description>\n";
  $output .= "    <content:encoded><![CDATA[";
  $output .= Markdown(htmlspecialchars($comment));
  $output .= "]]></content:encoded>\n";
  $output .= "    <dc:creator>";
  $output .= $author;
  $output .= "</dc:creator>\n";
  $output .= "    <pubDate>";
  $output .= date_format(date_create($date, timezone_open('GMT')), DATE_RFC2822);
  $output .= "</pubDate>\n";
  $output .= "  </item>\n";

  return $output;
}

function print_comments_feed($limit) {
  global $database;
  $output = "";

  try {
    $sql = $database->prepare('SELECT id, tag, author, date, comment FROM comments ORDER BY date DESC LIMIT 0, :limit');
    $sql->bindParam(':limit', $limit);

    if ($sql->execute()) {
      while ($row = $sql->fetch()) {
        $output .= print_comment_item($row['tag'], $row['id'], $row['author'], $row['date'], $row['comment']);
      }
    }
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
  return $output;
}

function whole_page() {
  global $domain;

  $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $output .= "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
  $output .= "<channel>\n";
  $output .= "  <title>Stacks project -- Comments</title>\n";
  $output .= "  <link>";
  $output .= $domain . href('comments-feed.rss');
  $output .= "</link>\n";
  $output .= "  <description>Stacks project, see http://stacks.math.columbia.edu</description>\n";
  $output .= "  <language>en</language>\n";
  $output .= "  <managingEditor>stacks.project@gmail.com (Stacks Project)</managingEditor>\n";
  $output .= "  <webMaster>pieterbelmans@gmail.com (Pieter Belmans)</webMaster>\n";
  $output .= "  <image>\n";
  $output .= "    <url>";
  $output .= $domain . href('stacks.png');
  $output .= "</url>\n";
  $output .= "    <title>Stacks project -- Comments</title>\n";
  $output .= "    <link>";
  $output .= $domain . href('comments-feed.rss');
  $output .= "</link>\n";
  $output .= "  </image>\n";
  $output .= print_comments_feed(10);
  $output .= "</channel>\n";
  $output .= "</rss>";

  return $output;
}

  header('Content-type: application/rss+xml');
  print whole_page();

?>
