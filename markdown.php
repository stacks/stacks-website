<!doctype html>
<?php
  include('config.php');
?>
<html>
  <head>
    <title>Stacks Project -- Markdown</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>

    <h2>Introduction</h2>
    <p><a href="http://daringfireball.net/projects/markdown/">Markdown</a> is a markup language designed to be intuitive. In a way it tries to mimick what you do if you are writing an email and you're adding emphasis, titles, lists, etc. An example:
    <pre><code>This is a title
===============
This is a [link](http://stacks.columbia.edu). A _little emphasis_, a *lot*.

* list
* list
* list

Some math: $\varphi\colon X\to Y$, see Tag \ref{04FW}.

1. another list
2. list
</code></pre>

    <p>You can use the preview option when writing a comment to check your comment before submit. Or if you use the fullscreen option you can see what your comment will look like in realtime.

    <h2>LaTeX and Markdown</h2>
    <p>Here at the Stacks project we try to merge Markdown and LaTeX for the comments system. You can write mathematics just like you're used in LaTeX, i.e. stuff like $\pi$ and<pre><code>\begin{equation}
  a^2+b^2=c^2
\end{equation}
</code></pre>
    just works. This is done using <a href="http://mathjax.org">MathJax</a> (so you need to enable Javascript).

    <p>MathJax also supports equation numbering through <code>\label{}</code> and <code>\ref{}</code> or <code>\eqref{}</code>, so you can number your equations and refer to them. This brings us to the next subject: Stacks Flavored Markdown.

    <h2>Stacks Flavored Markdown</h2>
    <p>Inspired by <a href="http://github.github.com/github-flavored-markdown/">GitHub Flavored Markdown</a> which is an extension of ordinary Markdown by things belonging specifically to GitHub (like referencing commits, users, etc.) we have created Stacks Flavored Markdown, or <abbr title="Stacks Flavored Markdown">SFM</abbr>.

    <p>An overview:

    <ol>
      <li>As discussed before you can use LaTeX the way you are used to.</li>

      <li>You can refer to tags by using <code>\ref{tag}</code>. So <code>\ref{0001}</code> will automatically be converted to <a href="<?php print(full_url('tag/0001')); ?>"><var>0001</var></a>. For this to trigger you just need <code>tag</code> to be a valid tag, it doesn't necessarily have to exist in the database.

      <li>It is also possible to refer to labels, as used in the Stacks project. This means that <code>\ref{topology-lemma-quasi-compact-closed-point}</code> gets parsed to <a href="<?php print(full_url('tag/005E')); ?>"><var>005E</var></a>. And if you write <code>\ref{lemma-quasi-compact-closed-point}</code> (i.e. without a chapter in front of the label) on a comment within the same chapter the system will understand you are referring to a "local" result and act accordingly.

        <p>Remark that it is <em>not encouraged</em> to use this type of referencing: tags are stable, labels are not. 
    </ol>

    <p>Note that only LaTeX works in the preview mode, uses of <code>\ref{}</code> will not be rendered.

    <h2>Remarks</h2>
    <p>The combination of Markdown, LaTeX and <abbr>SFM</abbr> probably contains bugs. Several external tools are needed for this setup to work, and there can be scenarios where it fails to produce the correct output. Also we can't guarantee that the preview looks exactly like your final comment.

    <p>If you think you've come across a bug in your comment, please mail the issue (and your comment id, which is visible on the tag lookup page) to <a href="mailto:stacks.project@gmail.com">stacks.project@gmail.com</a>.

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
