<!doctype html>
<?php
  include('config.php');
  include('functions.php');
?>
<html>
  <head>
    <title>Stacks Project -- Tags</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>The tag system</h2>
    <p>Each tag refers to a unique item (section, lemma, theorem, etc.) in order for this project to be referenceable. These tags don't change even if the item moves within the text.
    
    <h2>How to use it?</h2>
    <p>To find the tag for an item, hover/click on the item in the online pdf file or find the item in the tree view starting at <a href="<?php print(full_url('chapter/1')); ?>">Chapter 1</a>. See below for LaTeX instructions on how to reference a tag.

    <p>To find an item using a tag, <a href="<?php print(full_url('tag')); ?>">search for the tag's page</a>. The tag's page contains the location for the item referenced by the tag. It also contains its LaTeX code and a section for leaving comments.

    <h2>More information</h2>
    <p>The tag system provides stable references to definitions, lemmas, propositions, theorems, remarks, examples, exercises, situations and even equations, sections and items. As the project grows, each of these gets a tag which will always point to the same mathematical result. The place of the lemma in the document may change, the lemma may be moved to a different chapter, but its tag always keeps pointing to it.
    
    <p>If it ever turns out that a lemma, theorem, etc. was wrong then we may remove it from the project. However, we will keep the tag, and there will be an explanation for its disappearance (in the file tags mentioned below).

    <h2 id="reference">How to reference tags</h2>
    <p>In your BibTeX file put 
    <pre style="margin-left:1em"><code style="font-size:.9em">@misc{stacks-project, 
  author       = {The {Stacks Project Authors}}, 
  title        = {\itshape Stacks Project}, 
  howpublished = {\url{http://stacks.math.columbia.edu}}, 
  year         = {<?php print(date('Y')); ?>},
}</code></pre>
    Then you can use the citation code we provide on each tag's page (below the preview) to <em>cite</em> and <em>link</em> the corresponding tag, for example by
    <pre style="margin-left:1em"><code style="font-size:.9em">\cite[\href{http://stacks.math.columbia.edu/tag/0123}{Tag 0123}]{stacks-project}</code></pre>
    <p>This can be changed according to your tastes. In order to make the <code>\url</code> and <code>\href</code> commands to work, one should use the <a href="http://ctan.org/pkg/hyperref"><code>hyperref</code></a> package.</p>

    <h2>Technical information</h2>
    <p>There is a file called <a href="<?php print(full_url('tex/tags/tags')); ?>">tags</a> (in the <a href="https://github.com/stacks/stacks-project/tree/master/tags">tags subdirectory</a> of the actual Stacks project) which has on each line the tag followed by an identifier. Example:
    <pre style="margin-left:1em"><code>01MB,constructions-lemma-proj-scheme</code></pre>
    Here the tag is <var>01MB</var> and the identifier is <var>constructions-lemma-proj-scheme</var>. This means that the tag points to a lemma from the file <var>constructions.tex</var>. It currently has the label <var>lemma-proj-scheme</var>. If we ever change the lemma's  label, or move the lemma to a different file, then we will change the corresponding line in the file tags by changing the identifier correspondingly. But we will <strong>never change the tag</strong>. 

    <p>New tags are assigned by the maintainer of the project every once in a while using a script. A tag is a four character string made up out of digits and capital letters. They are ordered lexicographically between 0000 and ZZZZ giving 1679616 possible tags.

    <p>But as there might arise confusion from the similarities between <var>0</var> and <var>O</var> the letter <var>O</var> is no longer in use. This means that from <a href="<?php print(full_url('tag/04E6')); ?>">tag <var>04E6</var></a> on there are only 35 values per position. The 298 tags assigned before this new guideline will remain, as tags are constant. A little exercise for the reader: how many possible tags are there really?

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
