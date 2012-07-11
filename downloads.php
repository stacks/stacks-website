<!doctype html>
<?php
  include('config.php');
?>
<html>
  <head>
    <title>Stacks Project -- Downloads</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>

    <h2>Downloads</h2>
    <p>Let us know if there is some type of download you would like to see here. At this moment we provide both the <a href="#most-used">obvious candidates</a> such as the entire project in a <code>pdf</code> and a <a href="#listing">listing of <em>all</em> files in the project</a>.

    <h2 name="most-used">Most used links</h2>
    <ul>
      <li>the entire project in one file: <a href="<?php print(full_url('download/book.pdf')); ?>"><code>pdf</code></a> | <a href="<?php print(full_url('download/book.pdf')); ?>"><code>dvi</code></a>;
      <li>the links to <code>tex</code>, <code>pdf</code> and <code>dvi</code> files of all the chapters are on the <a href="<?php print(full_url('browse')); ?>">browse page</a>;
      <li>all chapters at once (tarred up): <a href="<?php print(full_url('archives/stacks-pdfs.tar')); ?>"><code>pdf</code></a> |  <a href="<?php print(full_url('archives/stacks-dvis.tar')); ?>"><code>dvi</code></a>;
      <li>the whole project as an <a href="<?php print(full_url('archives/stacks-project.tar.bz2')); ?>">archive</a>.
    </ul>    

    <h2 name="listing">Links to all files of the project</h2>
    <table id="browse">
      <tr>
        <th>name</th>
        <th>link</th>
      </tr>

<?php
  $output = '';
  exec('git submodule foreach git ls-files', $output);

  $tex_project = false;
  foreach ($output as $line) {
    if (substr($line, 0, 9) == 'Entering ') {
      $tex_project = ($line == 'Entering \'tex\'');
      continue;
    }

    if ($tex_project) {
?>
      <tr>
        <td><?php print($line); ?></td>
        <td><a href="<?php print(full_url('tex/' . $line)); ?>"><?php print($line); ?></a></td>
      </tr>
<?php
    }
  }
?>
    </table>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
