<?php

require_once("php/page.php");
require_once("php/general.php");

class ContributePage extends Page {
  public function getMain() {
    $output = "";

    $output .= "<h2>How to contribute</h2>";
    $output .= "<p>We welcome any kind of feedback: pointing out typos, mathematical errors, references in the literature, history of results, layout of webpages, spelling errors, improvements to the overall structure, missing lemmas, etc.  In fact, there are several different ways you can help:";
    $output .= "<ol>";
    $output .= "<li>If you are reading online and want to quickly point out something, please leave a comment on the webpage (click on the gray bar at the bottom of the page where it says \"Add a comment\").";
    $output .= "<li>If you prefer, you can simply email a small note to the address below.";
    $output .= "<li>It is very helpful if you edit the relevant TeX file directly and email the result, or even better: use Git and the <a href='http://github.com/stacks/stacks-project'>stacks-project</a> repository.";
    $output .= "<li>Take a look at this <a href='/todo'>todo list</a> and tackle one of the issues listed there.";
    $output .= "<li>You can <a href='/slogans'>suggest slogans for results</a>. Here the idea is that you come up with a sentence or two that describes the result in easily understandable language without using formulas.";
    $output .= "<li>You are encouraged to email expository papers. It is extremely useful to have such a text (no matter how badly written) as the startng point for a new chapter. Please do not worry about coding style, errors, gaps in the exposition, etc as the material will be radically changed anyway.";
    $output .= "</ol> Please be aware that all contributions are licensed under the <a href='https://github.com/stacks/stacks-project/blob/master/COPYING'>GNU Free Documentation License</a>.  </p>";

    $output .= "<h2>Where to submit</h2>";
    $output .= "<p>Please email contributions to <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a>. We will review, edit and if suitable update the Stacks project with your changes.</p>";
    $output .= "<p>If you are on the other hand <a href='#geeks'>familiar with Git and GitHub</a> it is also possible to make a pull request.</p>";

    $output .= "<h2>Instructions on dealing with TeX files</h2>";
    $output .= "<ol>";
    $output .= "<li>Download all the TeX files. They are contained in <a href='https://github.com/stacks/stacks-project/archive/master.zip'>this archive</a>.";
    $output .= "<li>Unpack the the archive (on Windows and Mac this should be automatic).";
    $output .= "<li>Edit the TeX file of the chapter you are interested in. Say <code>algebra.tex</code>.";
    $output .= "<li>Run <code>pdflatex algebra.tex</code>, then <code>bibtex algebra</code> and then <code>pdflatex algebra.tex</code> twice.";
    $output .= "<li>Inspect the result and if OK then email <code>algebra.tex</code> to the address above.";
    $output .= "</ol>";

    $output .= "<h2>Instructions on using the Makefile (slightly geeky)</h2>";
    $output .= "<p>Download and unpack the archive as above. Change directory to where you unpacked the files and on the command line type";
    $output .= "<pre><code>make pdfs </code></pre>";
    $output .= "<p>to automatically generate all the pdf files.";

    $output .= "<h2 id='geeks'>Instructions on using a version control system (for major geeks)</h2>";
    $output .= "<p>We are using the <a href='http://git-scm.com'>git</a> version control system. To clone the project type";
    $output .= "<pre><code>git clone git://github.com/stacks/stacks-project.git</code></pre>";
    $output .= "on the command line. This assumes that you have <code>git</code> installed. For more information on using Git and GitHub, we refer to <a href='http://git-scm.com/documentation'>the documentation</a> and <a href='https://help.github.com/'>GitHub Help</a>. To actually contribute the changes you have made you use a 'pull request' on GitHub.";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $contributors = $this->getContributors();
    
    $output .= "<h2>Contributors</h2>";
    $output .= "So far " . sizeof($contributors) . " people have contributed to the Stacks project, and these are:";
    //$output .= "<p style='margin: .5em; font-size: .8em'>";
    $output .= "<ul>";
    foreach ($contributors as $contributor)
      $output .= "<li style='margin: 0'>" . trim($contributor);
    $output .= "</ul>";

    return $output;
  }
  public function getTitle() {
    return " &mdash; How to contribute?";
  }

  private function getContributors() {
    global $config;

    $file = file($config["project"] . "/CONTRIBUTORS");
    return  array_slice($file, 4);
  }
}

?>


