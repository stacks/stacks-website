<?php

require_once("php/page.php");
require_once("php/general.php");

class ContributePage extends Page {
  public function getMain() {
    $output = "";

    $output .= "<h2>How to contribute</h2>";
    $output .= "<p>Any improvements are welcome. If you are reading the material and you find an error you can simply email a small note to the address below. This includes typos, spelling errors, improvements to the web pages, etc. If at all possible edit the relevant TeX file directly and email the result.";

    $output .= "<p>Here is a list of tasks you can try: <a href='" . href('todo') . "'>todo list</a>.";

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

    $output .= "<h2>Instructions in using the Makefile (slightly geeky)</h2>";
    $output .= "<p>Download and unpack the archive as above. Change directory to where you unpacked the files and on the command line type";
    $output .= "<pre><code>make pdfs </code></pre>";
    $output .= "to automatically generate all the pdf files. Similarly, type";
    $output .= "<pre><code>make dvis </code></pre>";
    $output .= "to create the dvi files instead.</p>";

    $output .= "<h2 id='geeks'>Instructions on using a version control system (for major geeks)</h2>";
    $output .= "<p>We are using the <a href='http://git-scm.com'>git</a> version control system. To clone the project type";
    $output .= "<pre><code>git clone git://github.com/stacks/stacks-project.git</code></pre>";
    $output .= "on the command line. This assumes that you have <code>git</code> installed. For more information on using Git and GitHub, we refer to <a href='https://help.github.com/'>GitHub Help</a>. To actually contribute the changes you have made you use a 'pull request' on GitHub.";

    return $output;
  }
  public function getSidebar() {
    $output = "";

    $contributors = $this->getContributors();
    
    $output .= "<h2>Contributors</h2>";
    $output .= "So far contributed " . sizeof($contributors) . " people have contributed to the Stacks project, and these are:";
    $output .= "<p style='margin: .5em; font-size: .8em'>";
    foreach ($contributors as $contributor)
      $output .= trim($contributor) . ($contributor != $contributors[sizeof($contributors) - 1] ? ", " : "");
    $output .= "</p>";

    return $output;
  }
  public function getTitle() {
    return "";
  }

  private function getContributors() {
    $file = file("../stacks-project/CONTRIBUTORS"); // TODO fix configuration
    return  array_slice($file, 4);
  }
}

?>


