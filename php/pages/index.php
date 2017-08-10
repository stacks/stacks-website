<?php

require_once("php/page.php");
require_once("php/comments.php");
require_once("php/feeds.php");
require_once("php/general.php");
require_once("php/search.php");
require_once("php/statistics.php");

require_once("php/simplepie/autoloader.php");

class IndexPage extends Page {
  public function getMain() {
    global $config;

    $value = "";
    $value .= "<h2><a href='" . href("about") . "'>About</a></h2>";
    $value .= "<p>This is the home page of the Stacks project. It is an open source textbook and reference work on algebraic stacks and the algebraic geometry needed to define them. For more general information see our extensive <a href='" . href("about") . "'>about page</a>.</p>";

    $value .= "<h2><a href='" . href("contribute") . "'>How to contribute?</a></h2>";

    $value .= "<p>The Stacks project is a collaborative effort. There is a <a href='https://github.com/stacks/stacks-project/blob/master/CONTRIBUTORS'>list of people who have contributed so far</a>. While browsing the Stacks project please provide feedback by leaving a comment. Another option is to suggest slogans for results. For more details please visit the <a href='/contribute'>contribute page</a>.</p>";

    $value .= "<h2><a href='" . href("browse") . "'>Browsing and downloads</a></h2>";
    $value .= "<p>The entire project in <a href='download/book.pdf'>one pdf file</a>. You can also <a href='" . href("browse") . "'>browse the project online</a>, and there is a tree view which starts at <a href='" . href("chapter/1") . "'>Chapter 1</a>. To download the source files there is <a href='https://github.com/stacks/stacks-project/'>stacks/stacks-project</a> at GitHub.</p>";

    $value .= "<h2><a href='" . href("tag") . "'>Looking up tags</a></h2>";
    $value .= "<p>You can search the Stacks project by keywords:";
    $value .= getSimpleSearchForm();
    $value .= "<p>If you on the other hand have a tag for an item (which can be anything, from section, lemma, theorem, etc.) in the Stacks project, you can <a href='" . href("tag") . "'>look up the tag's page</a>.</p>";

    $value .= "<h2><a href='" . href("tags") . "'>Referencing the Stacks project</a></h2>";
    $value .= "<p>Items (sections, lemmas, theorems, etc.) in the Stacks project are referenced by their tag. See the <a href='" . href("tags") . "'>tags explained page</a> to learn more about tags and how to reference them in a LaTeX document.</p>";

    $value .= "<h2>Leaving comments</h2>";
    $value .= "<p>You can leave comments on each and every tag's page. If you wish to stay updated on the comments, there is both a <a href='" . href("recent-comments") . "'>page containing recent comments</a> and an <a href='" . href("recent-comments.xml") . "' class='rss'>RSS feed</a> available.</p>";
    // TODO recent-comments.xml doesn't exist yet

    $value .= "<h2>Recent changes to the Stacks project</h2>";
    $value .= "<p>The Stacks project is hosted at GitHub, so you can <a href='https://github.com/stacks/stacks-project/commits/master'>browse the complete history</a> there.</p>";

    $value .= "<h2>License</h2>";
    $value .= "This project is licensed under the <a href='https://github.com/stacks/stacks-project/blob/master/COPYING'>GNU Free Documentation License</a>.";

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= getRecentChanges();
    $value .= getRecentBlogposts();
    $value .= getCommentsSidebar($this->db);
    $value .= getStatisticsSidebar($this->db);

    return $value;
  }
  public function getTitle() {
    return "";
  }
}

?>
