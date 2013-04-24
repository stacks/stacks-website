<?php

include("php/page.php");
include("php/comments.php");
include("php/general.php");

class IndexPage extends Page {
  public function getMain() {
    $value = "";
    $value .= "<h2><a href='#'>About</a></h2>";
    $value .= "<p>This is the home page of the Stacks project. It is an open source textbook and reference work on algebraic stacks and the algebraic geometry needed to define them. For more general information see our extensive <a href='#'>about page</a>.</p>";

    $value .= "<h2><a href='#'>How to contribute?</a></h2>";
    $value .= "<p>The Stacks project is a collaborative effort. There is a <a href='#'>list of people who have contributed so far</a>. If you would like to know how to participate more can be found at the <a href='#'>contribute page</a>. To informally comment on the Stacks project visit the <a href='#'>blog</a>.</p>";

    $value .= "<h2><a href='#'>Browsing and downloads</a></h2>";
    $value .= "<p>The entire project in one file: <a href='#'><var>pdf</var> version</a> | <a href='#'><var>dvi</var> version</a>. You can browse the project. There is a tree view which starts at <a href='#'>Chapter 1</a>. For other downloads (e.g. TeX files) we have a <a href='#'>dedicated downloads page</a>.</p>";

    $value .= "<h2><a href='#'>Looking up tags</a></h2>";
    $value .= "<p>You can search the Stacks project by keywords:";
    $value .= "<form action='#' method='get'>";
    $value .= "<label>Keywords: <input type='text' name='keywords'></label>";
    $value .= "<input type='submit' value='search'>";
    $value .= "</form>";
    $value .= "<p>If you on the other hand have a tag for an item (which can be anything, from section, lemma, theorem, etc.) in the Stacks project, you can <a href='#'>look up the tag's page</a>.</p>";

    $value .= "<h2><a href='#'>Referencing the Stacks project</a></h2>";
    $value .= "<p>Items (sections, lemmas, theorems, etc.) in the Stacks project are referenced by their tag. See the <a href='#'>tags explained page</a> to learn more about tags and how to reference them in a LaTeX document.</p>";

    $value .= "<h2>Leaving comments</h2>";
    $value .= "<p>You can leave comments on each and every tag's page. If you wish to stay updated on the comments, there is both a <a href='#'>page containing recent comments</a> and an <a href='#'>RSS feed <img src='images/rss.png'></a> available.</p>";

    $value .= "<h2>Recent changes to the Stacks project</h2>";
    $value .= "<p>The Stacks project is hosted at GitHub, so you can <a href='https://github.com/stacks/stacks-project/commits/master'>browse the complete history</a> there.</p>";

    $value .= "<h2>License</h2>";
    $value .= "This project is licensed under the <a href='#'>GNU Free Documentation License</a>.";

    return $value;
  }
  public function getSidebar() {
    $value .= "<h2><a href='" . href("recents-comments") . "'>Recent comments</a></h2>";
    $comments = get_comments($this->db, 0, 5);
    $value .= "<ol id='recent-comments-sidebar'>";
    foreach($comments as $comment) {
      $value .= "<li value='" . $comment["id"] . "'><a href='" . href("tag/" . $comment['tag'] . "#comment-" . $comment['id']) . "' title='" . $comment["date"] . "'>" . htmlentities($comment["author"]) . " on tag " . $comment["tag"] . "</a>";
    }
    $value .= "</ol>";

    $value .= "<h2>Recent additions</h2>";

    $value .= "<h2>Recent blog posts</h2>";

    $value .= "<h2>Highlights</h2>";
    // this could be pointers to blog posts discussing new functionality

    return $value;
  }
  public function getTitle() {
    return "";
  }
}

?>
