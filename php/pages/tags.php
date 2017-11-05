<?php

require_once("php/page.php");
require_once("php/general.php");

class TagsPage extends Page {
  public function getMain() {
    $value = "";

    $value .= "<h2>The tag system</h2>";
    $value .= "<p>Each tag refers to a unique item (section, lemma, theorem, etc.) in order for this project to be referenceable. These tags don't change even if the item moves within the text.";

    $value .= "<h2>How to use it?</h2>";
    $value .= "<p>To find the tag for an item, hover/click on the item in the <a href='download/book.pdf'>pdf file</a> or find the item in the tree view starting at <a href='" . href("chapter/1") . "'>Chapter 1</a>. See below for LaTeX instructions on how to reference a tag.";
    $value .= "<p>To find an item using a tag, <a href='" . href('tag') . "'>search for the tag's page</a>. The tag's page contains the location for the item referenced by the tag. It also contains its LaTeX code and a section for leaving comments.";

    $value .= "<h2>More information</h2>";
    $value .= "<p>The tag system provides stable references to definitions, lemmas, propositions, theorems, remarks, examples, exercises, situations and even equations, sections and items. As the project grows, each of these gets a tag which will always point to the same mathematical result. The place of the lemma in the document may change, the lemma may be moved to a different chapter, but its tag always keeps pointing to it.</p>";
    $value .= "<p>If it ever turns out that a lemma, theorem, etc. was wrong then we may remove it from the project. However, we will keep the tag, and there will be an explanation for its disappearance (in the file tags mentioned below).";

    $value .= "<h2 id='reference'>How to reference tags</h2>";
    $value .= "<p>In your BibTeX file put";
    $value .= "<pre><code>@misc{stacks-project,\n";
    $value .= "  shorthand    = {Stacks},\n";
    $value .= "  author       = {The {Stacks Project Authors}},\n";
    $value .= "  title        = {\\textit{Stacks Project}},\n";
    $value .= "  howpublished = {\url{http://stacks.math.columbia.edu}},\n";
    $value .= "  year         = {" . date('Y'). "},\n";
    $value .= "}</code></pre>";
    $value .= "Then you can use the citation code we provide on each tag's page (below the preview) to <em>cite</em> and <em>link</em> the corresponding tag, for example by";
    $value .= "<pre><code>\cite[\href{http://stacks.math.columbia.edu/tag/0123}{Tag 0123}]{stacks-project}</code></pre>";
    $value .= "<p>This can be changed according to your tastes. In order to make the <code>\url</code> and <code>\href</code> commands to work, one should use the <a href='http://ctan.org/pkg/hyperref'><code>hyperref</code></a> package. Some options are provided on the lookup page for a tag.</p>";

    $value .= "<h2>Technical information</h2>";
    $value .= "<p>There is a file called <a href='https://github.com/stacks/stacks-project/blob/master/tags/tags'><var>tags</var></a> (in the <a href='https://github.com/stacks/stacks-project/tree/master/tags'>tags subdirectory</a> of the Stacks project) which has on each line the tag followed by an identifier. Example:"; 
    $value .= "<pre><code>01MB,constructions-lemma-proj-scheme</code></pre>";
    $value .= "<p>Here the tag is <var>01MB</var> and the identifier is <var>constructions-lemma-proj-scheme</var>. This means that the tag points to a lemma from the file <var>constructions.tex</var>. It currently has the label <var>lemma-proj-scheme</var>. If we ever change the lemma's  label, or move the lemma to a different file, then we will change the corresponding line in the file tags by changing the identifier correspondingly. But we will <strong>never change the tag</strong>.</p>";

    $value .= "<p>A tag is a four character string made up out of digits and capital letters. They are ordered lexicographically between <var>0000</var> and <var>ZZZZ</var> originally giving 1679616 possible tags. But as there might arise confusion from the similarities between <var>0</var> and <var>O</var> it was decided to stop using the letter <var>O</var>. The last tag using <var>O</var> is <a href='" . href('tag/04DO') . "'>tag <var>04DO</var></a>. Thus from <var>04DP</var> on there are only 35 values per position. The 302 tags assigned before this new guideline will remain, as tags are constant.";

    $value .= "<h2 id='stacks-epoch'>Stacks epoch</h2>";
    $value .= "<p>The first 3026 tags were introduced in the Stacks project on <a href='https://github.com/stacks/stacks-project/commit/fad2e125112d54e1b53a7e130ef141010f9d151d'>May 16, 2009</a>. New tags are assigned by the maintainer of the Stacks project every once in a while using a script. As the Stacks project is always under construction the available tags on the website, in the git repository, and the available results in the Stacks project can sometimes be a little bit out of sync.";

    return $value;
  }
  public function getSidebar() {
    $value = "";

    $value .= getStatisticsSidebar($this->db);

    $value .= "<h2>Tag lookup</h2>";
    $value .= printTagLookup(10);
    $value .= "<p style='clear: both'>";
    $value .= "<h2>Search</h2>";
    $value .= getSimpleSearchForm(false, 10);

    return $value;
  }
  public function getTitle() {
    return " &mdash; Tags explained";
  }
}

?>

