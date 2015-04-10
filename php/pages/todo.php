<?php

require_once("php/page.php");
require_once("php/general.php");
require_once("php/markdown/markdown.php");

class TodoPage extends Page {
  public function getHead() {
    $output = "";

    $output .= printMathJax();
    
    return $output;
  }

  public function getMain() {
    global $config;
    $output = "";

    $output .= "<h2>Important</h2>";
    $output .= "<p>Before hacking away and spending enormous amounts of time on a project for the Stacks project, choose a smaller task, say something you can do in 5 minutes up to an hour. Email the result (usually the modified TeX file) to <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a> and see what it feels like to donate some of your own work to a publicly maintained project. Having done this successfully you can try your hand at some more ambitious projects.";
    $output .= "<p>Also, it is very helpful if you try to keep to the coding style which is used throughout the TeX files.";

    $output .= "<h2 id='beer'>Tasks you can do while having a beer</h2>";
    $output .= "<ol>";
    $output .= "<li>Run any of the TeX files through a spell checker and correct any errors.";
    $output .= "<li>Find incompatible notation and correct it. The same mathematical object should be coded in the same way everywhere.";
    $output .= "<li>Read a random section and find small mathematical errors, such as arrows pointing the wrong way, wrong font, sign errors, etc. If they are small enough you can simply correct them. Otherwise, just email what's wrong.";
    $output .= "<li>Provide counterexamples for silly statements. For example, find a Noetherian ring which is not of finite type over a field, namely $\mathbb{Z}$. (Just to give you an idea.)";
    $output .= "<li>Basic notions. Write something basic about algebra, topology, fields, etc. which goes in an early part (and hasn't been written yet).";
    $output .= "</ol>";
    $output .= "<p>For many of these tasks the commenting system available on the website suffices. Just look up the tag and post a comment, we will deal with the actual change in the Stacks project.</p>";

    $output .= "<h2 id='tea'>Tasks you can do while having tea</h2>";
    $output .= "<ol>";
    $output .= "<li>Provide missing proofs of easy statements which have been omitted. To find these do a case-insensitive search for the string <q>omit</q> in the text. If you hit on a omitted proof which you find too hard, then please report this.";
    $output .= "<li>Check for missing internal references. Generally speaking the goal is to refer to all of the previous lemmas, propositions, theorems used in a proof. Go through some of the proofs and check if previous results are used without referencing them.";
    $output .= "<li>Find mathematical mistakes.";
    $output .= "<li>Find superfluous assumptions.";
    $output .= "<li>Find missing assumptions.";
    $output .= "<li>Specific example of 2): Find all places where it is used that an &eacute;tale morphism of schemes is locally quasi-finite and put in a reference to <var>lemma-etale-locally-quasi-finite</var>.";
    $output .= "</ol>";
    $output .= "<p>Again, many of these can be done using the commenting system.</p>";
    
    $output .= "<h2 id='coffee'>Tasks you can do while having coffee</h2>";
    $output .= "<ol>";
    $output .= "<li>Split longer proofs into pieces by finding intermediate results.";
    $output .= "<li>Find alternative proofs (but beware of creating circular arguments).";
    $output .= "<li>Write introductions, overviews of already existing material.";
    $output .= "<li>Add sections on your favorite topic. For example: You may be interested in curves. Start a chapter entitled <q>Curves</q>. For example you can provide atheorem saying that the category of curves (with dominant rational maps) over a field \$k\$ is equivalent to the category of finitely generated field extensions transcendence degree 1 over \$k\$.";
    $output .= "</ol>";

    $output .= "<h2 id='difficult'>More difficult tasks</h2>";
    $output .= "<p>What you see here is the current status of the file <a href='https://github.com/stacks/stacks-project/blob/master/documentation/todo-list'><var>todo-list</var> in the project</a>.";
    $output .= "<ol>";

    $file = file_get_contents($config["project"] . "/documentation/todo-list");
    $items = explode("\n\n\n", $file);
    foreach ($items as $item) {
      // PHP Markdown isn't perfect on the current content of the file
      $item = str_replace('<em>', '_', Markdown($item));
      $item = str_replace('</em>', '_', $item);
      // remove superfluous paragraphs (could do this in CSS too)
      $item = str_replace('<p>', '', $item);
      $item = str_replace('</p>', '', $item);

      $output .= "<li>" . $item;
    }

    $output .= "</ol>";

    $output .= "<h2 id='maintenance'>Maintenance</h2>";
    $output .= "<p><strong>Contact the maintainer at the email address above before attempting these!</strong>";
    $output .= "<ol>";
    $output .= "<li>Split algebra chapter in two (this is hard to do without messing up the tags system).";
    $output .= "<li>Improve the Makefile.";
    $output .= "<li>Clean up Python scripts.";
    $output .= "<li>Improve consistency of notation. Example: 'known' categories such as Sets, Groups, Sheaves, Abelian Sheaves etc are not named in a consistent manner.";
    $output .= "<li>Find people willing to mirror the project online, preferably in a very different geographical location. If you are interested and a major geek please contact via the email address above.";
    $output .= "</ol>";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $output .= "<h2>Categories</h2>";
    $output .= "<ul>";
    $output .= "<li><a href='#beer'>Tasks while having a beer</a>";
    $output .= "<li><a href='#tea'>Tasks while having tea</a>";
    $output .= "<li><a href='#coffee'>Tasks while having coffee</a>";
    $output .= "<li><a href='#difficult'>Difficult tasks</a>";
    $output .= "<li><a href='#maintenance'>Maintenance</a>";
    $output .= "</ul>";

    return $output;
  }
  public function getTitle() {
    return " &mdash; Overview of things to do";
  }
}

?>


