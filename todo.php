<!doctype html>
<?php
  include('config.php');
  include('functions.php');
  include('php-markdown-extra-math/markdown.php');
?>
<html>
  <head>
    <title>Stacks Project -- Todo</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">

    <script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <script type="text/x-mathjax-config">
      MathJax.Hub.Config({
        tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
      });
    </script>
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>Important</h2>
    <p>Before hacking away and spending enormous amounts of time on a project for the Stacks project, choose a smaller task, say something you can do in 5 minutes up to an hour. Email the result (usually the modified TeX file) to <a href="mailto:stacks.project@gmail.com">stacks.project@gmail.com</a> and see what it feels like to donate some of your own work to a publicly maintained project. Having done this successfully you can try your hand at some more ambitious projects.
    <p>Also, it is very helpful if you try to keep to the coding style which is used throughout the TeX files.

    <h2>Tasks you can do while having a beer</h2>
    <ol>
      <li>Run any of the TeX files through a spell checker and correct any errors.
      <li>Find incompatible notation and correct it. The same mathematical object should be coded in the same way everywhere.
      <li>Read a random section and find small mathematical errors, such as arrows pointing the wrong way, wrong font, sign errors, etc. If they are small enough you can simply correct them. Otherwise, just email what's wrong.
      <li>Provide counterexamples for silly statements. For example, find a Noetherian ring which is not of finite type over a field, namely $\mathbb{Z}$. (Just to give you an idea.)
      <li>Basic notions. Write something basic about algebra, topology, fields, etc. which goes in an early part (and hasn't been written yet).
      <li>Find ocurrences of <code>\coprod</code> and <code>\amalg</code> and consistently have the following
        <pre><code>A \amalg B
\coprod_{i\in I} A_i
A \amalg \coprod_{i\in I} B_i</code></pre>
        i.e., use <code>\amalg</code> if there are only two and <code>\coprod</code> if there are more. The last one doesn't look good, but <code>\coprod \coprod_i</code> is even worse!
    </ol>
    
    <h2>Tasks you can do while having tea</h2>
    <ol>
      <li>Provide missing proofs of easy statements which have been omitted. To find these do a case-insensitive search for the string <q>omit</q> in the text. If you hit on a omitted proof which you find too hard, then please report this.
      <li>Check for missing internal references. Generally speaking the goal is to refer to all of the previous lemmas, propositions, theorems used in a proof. Go through some of the proofs and check if previous results are used without referencing them.
      <li>Find mathematical mistakes.
      <li>Find superfluous assumptions.
      <li>Find missing assumptions.
      <li>Specific example of 2): Find all places where it is used that an &eacute;tale morphism of schemes is locally quasi-finite and put in a reference to <var>lemma-etale-locally-quasi-finite</var>.
    </ol>

    <h2>Tasks you can do while having coffee</h2>
    <ol>
      <li>Split longer proofs into pieces by finding intermediate results.
      <li>Find alternative proofs (but beware of creating circular arguments).
      <li>Write introductions, overviews of already existing material.
      <li>Add sections on your favorite topic. For example: You may be interested in curves. Start a chapter entitled <q>Curves</q>. For example you can provide atheorem saying that the category of curves (with dominant rational maps) over a field $k$ is equivalent to the category of finitely generated field extensions
of transcendence degree 1 over $k$.
    </ol>

    <h2>More difficult tasks</h2>
<?php
  $todolist = 'tex/documentation/todo-list';
?>
    <p>What you see here is the current status of the file <a href="<?php print(full_url($todolist)); ?>"><var>todo-list</var> in the project</a>.
    <ol>
<?php
  $file = file_get_contents($todolist);
  $items = explode("\n\n\n", $file);
  foreach ($items as $item) {
    // PHP Markdown isn't perfect on the current content of the file
    $item = str_replace('<em>', '_', Markdown($item));
    $item = str_replace('</em>', '_', $item);
    // remove superfluous paragraphs (could do this in CSS too)
    $item = str_replace('<p>', '', $item);
    $item = str_replace('</p>', '', $item);
    print("      <li>" . $item . "\n");
  }
?>
    </ol>

    <h2>Maintenance</h2>
    <p><strong>Contact the maintainer at the email address above before attempting these!</strong>
    <ol>
      <li>Split algebra chapter in two (this is hard to do without messing up the tags system).
      <li>Improve the Makefile.
      <li>Clean up Python scripts.
      <li>Prettify the website.
      <li>Improve consistency of notation. Example: "known" categories such as Sets, Groups, Sheaves, Abelian Sheaves etc are not named in a consistent manner.
      <li>Setup and run a mailing list.
      <li>Setup and run a bug system; mainly for feature requests.
      <li>Setup and run a sign off system, where collaborators can sign off on results in the stacks project, i.e., saying <q>I declare this is true</q>, and where in addition we can put links to similar results in the literature.
      <li>Find people willing to mirror the project online, preferably in a very different geographical location. If you are interested and a major geek please contact via the email address above. This is related to (2), (3) and (4) above.
      <li>Instead of (6), (7), (8) have a system for visitors of the website to leave comments, which are archived and visible (as the comments left on a blog for example). Some of these can be labeled as bugs, some as feature requests, some as declarations of correctness, etc.
    </ol>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
