<?php

require_once("php/page.php");
require_once("php/statistics.php");
require_once("php/general.php");

class APIPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>Overview of the <abbr title='Application Programming Interface'>API</abbr></h2>";
    $value .= "<p>It is possible to query the Stacks project yourself through an <abbr>API</abbr>. This way you don't have to scrape the information from the <abbr title='HyperText Markup Language'>HTML</abbr> pages and it is consistent with our goal that the content of the Stacks project be as open as possible.";
    $value .= "<p>We can think of several applications:";
    $value .= "<ul>";
    $value .= "<li>developing smartphone apps / mobile versions of the Stacks website;";
    $value .= "<li>extracting meta-information about the Stacks project;";
    $value .= "<li>creating your own graphs;";
    $value .= "<li>... (please make suggestions!)";
    $value .= "</ul>";
    $value .= "<p>If you intend to use this <abbr>API</abbr>, please contact us at <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>. <strong>This interface is not stable yet, please get in touch to discuss this with us</strong>.";
    $value .= "<p>At the moment there are two types of information accessible through the interface:";
    $value .= "<ol>";
    $value .= "<li><a href='#statements'>statements</a> of the tags (in various flavours);";
    $value .= "<li>the <a href='#graphs'>data for graphs</a>.";
    $value .= "</ol>";
    $value .= "<p>We are <a href='#suggestions'>open to suggestions</a>, please <a href='mailto:stacks.project@gmail.com'>get in touch</a> if you want to use this interface and if you have comments.";

    $value .= "<h2 id='statements'>Statements</h2>";
    $value .= "<p>There are four different possibilities to get the statement of a tag:";
    $value .= "<ol>";
    $value .= "<li><abbr title='HyperText Markup Language'>HTML</abbr>, <em>without</em> proof:";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/content/statement</code></pre>";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/content/statement'><abbr>HTML</abbr> statement for tag </var>015I</var></a>";
    $value .= "<li><abbr title='HyperText Markup Language'>HTML</abbr>, <em>with</em> proof:";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/content/full</code></pre>";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/content/full'><abbr>HTML</abbr> statement (with proof) for tag </var>015I</var></a>";
    $value .= "<li>LaTeX, <em>without</em> proof:";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/content/statement/raw</code></pre>";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/content/statement/raw'>LaTeX statement for tag </var>015I</var></a>";
    $value .= "<li>LaTeX, <em>with</em> proof:";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/content/full/raw</code></pre>";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/content/full/raw'>LaTeX statement (with proof) for tag </var>015I</var></a>";
    $value .= "</ol>";
    $value .= "<p>Due to the way the content is parsed and inserted in the database, the LaTeX output contains <abbr>HTML</abbr> links for references. If you wish to use this type of output without this small nuisance, please contact us.";
    $value .= "<p>Because the mathematics is parsed by MathJax, the <abbr>HTML</abbr> output contains raw LaTeX math. It is up to the user to handle this (e.g. either use MathJax, or some image generation tool).";

    $value .= "<h2 id='graphs'>Graphs</h2>";
    $value .= "<p>The dependency graphs for every tag are generated using <a href='http://d3js.org'>D3.js</a>. This JavaScript library uses <abbr title='JavaScript Object Notation'>JSON</abbr> files to render the graphs. But if you want to create your own visualisations (or extract statistical information!) you can use these files too, they're there anyway.";
    $value .= "<p>There are three types of graphs, each with their own data structure:";
    $value .= "<ol>";
    $value .= "<li>force-directed:";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/graph/force</code></pre>";
    $value .= "<p>This graph contains <em>all nodes</em> of the dependency graph of a result. It consists of a list of nodes corresponding to tags (together with some meta-information) and a list of edges. These graphs are directed acyclic graphs, no multiple nodes.";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/graph/force'>data for the force-directed graph of tag </var>015I</var></a>";
    $value .= "<li>cluster";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/graph/cluster</code></pre>";
    $value .= "<p>This graph contains a subset of the nodes (at most either 6 levels deep, or 150 nodes) and is a <em>tree</em>. It contains the dependencies as a nested structure. There are repeated nodes (if a result is used in two different tags it is repeated because it is a tree). Again, there is meta-information contained in the <abbr>JSON</abbr> file.";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/graph/cluster'>data for the clustered graph of tag </var>015I</var></a>";
    $value .= "<li>collapsible (or: per chapter)";
    $value .= "<pre><code>http://stacks.math.columbia.edu/data/tag/&lsaquo;<em>tag</em>&rsaquo;/graph/collapsible</code></pre>";
    $value .= "<p>This graph groups results first by chapter, then by section. Hence it is a tree of 4 levels deep. It contains all tags of the dependency graphs, but of course the logical dependencies are not visible. Whenever a tag is a section, it is contained in the graph both on the lowest level (as a tag) and on the second-to-lowest level (as a section). Again, there is meta-information contained in the <abbr>JSON</abbr> file.";
    $value .= "<p>example: <a href='http://stacks.math.columbia.edu/data/tag/015I/graph/collapsible'>data for the collapsible graph of tag </var>015I</var></a>";
    $value .= "</ol>";

    $value .= "<h2 id='suggestions'>Suggestions?</h2>";
    $value .= "<p>This is just a preliminary version. It was created because we needed it ourselves (for the graphs and the previews of the tags in the graphs). Hence, if you have a feature request, send an email to <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a> and we'll see what we can do.";

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
    return " &mdash; API";
  }
}

?>

