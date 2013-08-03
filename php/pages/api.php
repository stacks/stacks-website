<?php

require_once("php/page.php");
require_once("php/statistics.php");
require_once("php/general.php");

class APIPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>Overview</h2>";
    $value .= "<p>It is possible to query the Stacks project yourself through an <abbr title='Application Programming Interface'>API</abbr>. This way you don't have to scrape the information from the <abbr title='HyperText Markup Language'>HTML</abbr> pages, because we want the content of the Stacks project to be as open as possible.";
    $value .= "<p>We think about several applications of this:";
    $value .= "<ul>";
    $value .= "<li>developing smartphone apps / mobile versions;";
    $value .= "<li>extracting meta-information about the Stacks project;";
    $value .= "<li>creating your own graphs;";
    $value .= "<li>... (please do make suggestions!)";
    $value .= "</ul>";
    $value .= "<p>If you intend to use this <abbr>API</abbr>, please contact us at <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>. <strong>This interface is not stable yet, please get in touch to discuss this with us</strong>.";
    $value .= "<p>At this moment there are two main ways of interfacing with the Stacks project:";
    $value .= "<ol>";
    $value .= "<li><a href='#statements'>statements</a> of the tags</a>";
    $value .= "<li><a href='#graphs'>graphs</a>";
    $value .= "</ol>";
    $value .= "<p>We are open to suggestions, please get in touch if you want to use this interface.";

    $value .= "<h2 id='statements'>Statements</h2>";
    $value .= "<p>";

    $value .= "<h2 id='graphs'>Graphs</h2>";
    $value .= "<p>";

    $value .= "<h2>Suggestions?</h2>";
    $value .= "<p>";

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= getRecentChanges();
    $value .= getRecentBlogposts();
    $value .= getStatisticsSidebar($this->db);

    return $value;
  }
  public function getTitle() {
    return " &mdash; About";
  }
}

?>

