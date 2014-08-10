<?php
require_once("php/config.php");

// parse an Atom / RSS feed and output $length items as a list
function getFeed($url, $length) {
  global $config;
  $value = "";

  $value .= "<ul>";

  $feed = new SimplePie();
  $feed->set_cache_location($config["SimplePie cache"]); 
  $feed->set_feed_url($url);
  $feed->init();
  $feed->handle_content_type();

  foreach ($feed->get_items(0, 5) as $item)
    $value .= "<li>" . $item->get_date() . ":<br> <a href='" . $item->get_link() . "'>" . $item->get_title() . "</a></li>";

  $value .= "</ul>";
  
  return $value;
}

// recent blog posts, for use in a sidebar
function getRecentBlogPosts() {
  global $config;
  $value = "";

  $value .= "<h2><a href='" . $config["blog feed"] . "' class='rss'>Recent blog posts</a></h2>";
  $value .= getFeed($config["blog feed"], 5);

  return $value;
}

// recent changes to the 'master' branch, for use in a sidebar
function getRecentChanges() {
  global $config;
  $value = "";

  $value .= "<h2><a href='" . $config["GitHub feed"] . "' class='rss'>Recent changes</a></h2>";
  $value .= getFeed($config["GitHub feed"], 5);

  return $value;
}

?>
