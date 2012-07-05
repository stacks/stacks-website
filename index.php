<!doctype html>
<?php
  include('config.php');
?>
<html>
  <head>
    <title>Stacks Project</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>

    <h2><a href="<?php print(full_url('about')); ?>">About</a></h2>
    <p>This is the home page of the Stacks project. It is an open source textbook and reference work on algebraic stacks and the algebraic geometry needed to define them. For more general information click we have an <a href="<?php print(full_url('about')); ?>">extensive about page</a>.

    <h2><a href="<?php print(full_url('contribute')); ?>">How to contribute</a></h2>
    <p>The Stacks project is a collaborative effort. There is a <a href="tex/CONTRIBUTORS">list of people who have contributed so far</a>. If you would like to know how to participate more can be found at the <a href="<?php print(full_url('contribute')); ?>">contribute page</a>. To informally comment on the Stacks project visit the <a href="http://math.columbia.edu/~dejong/wordpress/">blog</a>.

    <h2><a href="<?php print(full_url('downloads')); ?>">Downloads</a></h2>
    <p>You can download the entire project in one file: <a href="<?php print(full_url('tex/book.pdf')); ?>">pdf version</a> | <a href="<?php print(full_url('tex/book.dvi')); ?>">dvi version</a>. It is also possible to <a href="<?php print(full_url('browse')); ?>">browse the project one chapter at a time</a>. For other downloads (e.g. TeX files) we have a <a href="<?php print(full_url('downloads')); ?>">dedicated downloads page</a>.

    <h2><a href="<?php print(full_url('tag')); ?>">Looking up and referencing results</a></h2>
    <p>We have a page about <a href="<?php print(full_url('tags')); ?>">referencing results</a> in the Stacks project. And it is possible to <a href="<?php print($directory . 'tag'); ?>">search for tags</a> in the project, which gives the location, corresponding LaTeX code and the possibility to comment on it. In case you know the deal, you can search directly:

    <form action="<?php print(full_url('search.php')); ?>" method="post">
      <label>Tag: <input type="text" name="tag"></label>
      <input type="submit" value="locate">
    </form>
    <br>

    <h2><a href="http://paard.math.columbia.edu:8888/stacks.git">Recent changes</a></h2>
    <p>You can either see the <a href="<?php print(full_url('tex/log.log')); ?>">last 50 log entries in plaintext</a> or <a href="http://paard.math.columbia.edu:8888/stacks.git">browse the complete history</a>.

    <h2><a href="<?php print(full_url('tex/COPYING')); ?>">License</a></h2>
    <p>This project is licensed under the <a href="<?php print(full_url('tex/COPYING')); ?>">GNU Free Documentation License</a>.
  </body>
</html>

