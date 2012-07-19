<!doctype html>
<?php
  include('config.php');
  include('functions.php');
?>
<html>
  <head>
    <title>Stacks Project -- Contributing</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>How to contribute</h2>
    <p>Any improvements are welcome. If you are reading the material and you find an error you can simply email a small note to the address below. This includes typos, spelling errors, improvements to the web pages, etc. If at all possible edit the relevant TeX file directly and email the result.

    <p>Here is a list of tasks you can try: <a href="<?php print(full_url('todo')); ?>">todo list</a>.

    <h2>Where to submit</h2>
    <p>Please email contributions to <a href="mailto:stacks.project@gmail.com">stacks.project@gmail.com</a>. We will review, edit and if suitable update the Stacks project with your changes.

    <h2>Instructions on dealing with TeX files</h2>
    <ol>
      <li>Download all the TeX files. They are contained in <a href="<?php print(full_url('tex/stacks-git.tar.bz2')); ?>">this archive</a>.
      <li>Unpack the the archive (on Windows and Mac this should be automatic).
      <li>Edit the TeX file of the chapter you are interested in. Say <code>algebra.tex</code>.
      <li>Run <code>latex algebra.tex</code>, then <code>bibtex algebra</code> and then <code>latex algebra.tex</code> twice.
      <li>Inspect the result and if OK then email <code>algebra.tex</code> to the address above.
    </ol>

    <h2>Instructions in using the Makefile (slightly geeky)</h2>
    <p>Download and unpack the archive as above. Change directory to where you unpacked the files and on the command line type 
    <pre><code>make pdfs </code></pre>
    to automatically generate all the pdf files. Similarly, type
    <pre><code>make dvis </code></pre>
    to create the dvi files instead.</p>


    <h2>Instructions on using a version control system (for major geeks)</h2>
    <p>We are using the <a href="http://git-scm.com">git</a> version control system. To clone the project type
    <pre><code>git clone git://github.com/stacks/stacks-project.git</code></pre>
    on the command line. This assumes that you have git installed. See this file for a <a href="<?php print(full_url('git-howto')); ?>">git howto</a>. And it is possible to inspect the <a href="https://github.com/stacks/stacks-project/commits/master">development history of the project</a>.

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
