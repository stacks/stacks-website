<!doctype html>
<?php
  include('config.php');
  include('functions.php');
?>
<html>
  <head>
    <title>Stacks Project -- Acknowledgements</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>
  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>Acknowledgements</h2>

<?php
  $acknowledgements = 'tex/documentation/support';
?>
    <p>What you see here is the current status of the file <a href="<?php print(full_url($acknowledgements)); ?>"><var>support</var> in the project</a>.
    <ol>
<?php
  $file = file_get_contents($acknowledgements);
  $items = explode("\n\n\n", $file);
  foreach ($items as $item) {
    $item = str_replace('<em>', '_', $item);
    $item = str_replace('</em>', '_', $item);
    $item = str_replace('<p>', '', $item);
    $item = str_replace('</p>', '', $item);
    print("      <li>" . $item . "\n");
  }
?>
    </ol>
  </body>
</html>
