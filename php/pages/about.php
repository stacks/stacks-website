<?php

require_once("php/page.php");
require_once("php/comments.php");
require_once("php/statistics.php");
require_once("php/general.php");

class AboutPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>About</h2>";
    $value .= "<p>The Stacks project started in 2005. The initial idea was for it to be a collaborative web-based project with the aim of writing an introductory text about algebraic stacks. Temporarily there was a mailing list and some discussion as to how to proceed. For example, there are issues with referencing such a document, how to distribute credit, who does what, and many more. Although we have definite ideas about most of these points we would like to take a more positive approach. Namely, to simply create something and solve problems and answer questions as they come up.</p>";
    $value .= "<hr>";
    $value .= "<p>We do want to answer a few basic questions that the casual visitor may have about this project:</p>";
    $value .= "<ol>";
    $value .= "<li>The Stacks project is no longer an introductory text, but aims to build up enough basic algebraic geometry as foundations for algebraic stacks. This implies a good deal of theory on commutative algebra, schemes, varieties, algebraic spaces, has to be developed en route.";
    $value .= "<li>The Stacks project has a maintainer (currently <a href='http://www.math.columbia.edu/~dejong/'>Aise Johan de Jong</a>) who accepts changes etc. proposed by contributors. Although everyone is encouraged to participate it is not a wiki.";
    $value .= "<li>The Stacks project is meant to be read online, and therefore we do not worry about length of the chapters, etc. Moreover, with hyperlinks it is possible to quickly browse through the chapters to find the lemmas, theorems, etc. that a given result depends on.";
    $value .= "</ol>";

    $value .= "<h2><a href='" . href("acknowledgements") . "'>Acknowledgements</a></h2>";
    $value .= "<p>We have a page <a href='" . href("acknowledgements") . "'>acknowledging support</a>.";

    $value .= "<h2>The Stacks project, its website, and tools</h2>";
    $value .= "<p>There are currently three open source repositories tracking development for the Stacks project and its website:</p>";
    $value .= "<ol>";
    $value .= "<li>The LaTeX files making up the Stacks project itself can be found <a href='https://github.com/stacks/stacks-project'>here</a>.";
    $value .= "<li>The website is being developed as a <a href='https://github.com/stacks/stacks-website'>separate project</a>. It is currently maintained by <a href='http://pbelmans.wordpress.com/'>Pieter Belmans</a>.";
    $value .= "<li>There is a <a href='https://github.com/stacks/stacks-tools'>repository</a> containing tools and infrastructure used by both the project and the website.";
    $value .= "</ol>";
    $value .= "<p>If you wish to start your own project, inspired by the Stacks project we hope the combination of these projects can serve as a starting point. If you have any questions about this, please do not hesitate to send an email to <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>.";

    $value .= "<h2><a href='" . href("api") . "'>API</a></h2>";
    $value .= "<p>You can query the Stacks project through an <a href='" . href("api") . "'><abbr title='Application Programming Interface'>API</abbr></a>.";

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
    return " &mdash; About";
  }
}

?>
