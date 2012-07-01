<!doctype html>
<?php
  include('config.php');
?>
<html>
  <head>
    <title>Stacks Project -- Tags</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>

    <h2>The tag system</h2>
    <p>Each tag refers to a unique lemma, theorem, etc. in order for this project to be referenceable. These tags don't change even if the lemma (or theorem, etc.) moves within the text.
    
    <h2>How to use it</h2>
    <p>To look up a lemma, theorem etc. using a tag, just go to the <a href="<?php print(full_url('tag')); ?>">search page</a> and input the tag in the box.

    <p>To reference a result in the stacks project find its corresponding tag by hovering/clicking on the lemma, theorem, etc. in the online pdf file. See below for LaTeX instructions.

    <h2>More information</h2>
    <p>The tag system provides stable references to definitions, lemmas, propositions, theorems, remarks, examples, exercises, situations and even equations, sections and items. As the project grows, each of these gets a tag which will always point to the same mathematical result. The place of the lemma in the document may change, the lemma may be moved to a different chapter, but its tag always keeps pointing to it.
    
    <p>If it ever turns out that a lemma, theorem, etc. was wrong then we may remove it from the project. However, we will keep the tag, and there will be an explanation for its disappearance (in the file tags mentioned below).

    <h2>How to reference tags in LaTeX documents</h2>
    <p>In your BibTeX file put 
    <pre><code>@MISC{stacks-project, 
  AUTHOR = "The {Stacks Project Authors}", 
  TITLE = "{\itshape Stacks Project}", 
  HOWPUBLISHED = "\url{http://math.columbia.edu/algebraic_geometry/stacks-git}", 
}
    </code></pre>
    <p>Then you can use a construction such as
    <pre><code>\cite[Definition 0123]{stacks-project}</code></pre>
    to reference the tag. If you feel couragous you can go ahead and make 0123 a link to the stable url by the following construction
    <!-- TODO already anticipating new URL scheme -->
    <pre><code>\href{http://math.columbia.edu/algebraic_geometry/stacks-git/tag/0123}{0123}</code></pre>

    <h2>Technical information</h2>
    <p>There is a file called <a href="<?php print(full_url('tex/tags/tags')); ?>">tags</a> (in the <a href="<?php print(full_url('tex/tags')); ?>">tags subdirectory</a>) which has on each line the tag followed by an identifier. Example: 
    <pre><code>01MB,constructions-lemma-proj-scheme</code></pre>
    Here the tag is <var>01MB</var> and the identifier is <var>constructions-lemma-proj-scheme</var>. This means that the tag points to a lemma from the file <var>constructions.tex</var>. It currently has the label <var>lemma-proj-scheme</var>. If we ever change the lemma's  label, or move the lemma to a different file, then we will change the corresponding line in the file tags by changing the identifier correspondingly. But we will <strong>never change the tag</strong>. 

    <!-- TODO remark that 0 and O are handled in a special way, do the math -->
    <p>New tags are assigned by the maintainer of the project every once in a while using a script. A tag is a four character string made up out of digits and capital letters. They are ordered lexicographically between 0000 and ZZZZ giving 1679616 possible tags. That should be enough for a while!

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
