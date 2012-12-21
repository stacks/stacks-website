<!doctype html>
<?php
  include('config.php');
  include('functions.php');

  try {
    $db = new PDO(get_database_location());
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  // print a row of the table containing a chapter
  function print_chapter($chapter, $filename, $number) {
?>
      <tr> 
        <td></td> 
        <td><?php print($number . ".&nbsp;&nbsp;&nbsp;" . $chapter); ?></td> 
        <td><a href="<?php print(full_url('chapter/' . $number)); ?>"><code>online</code></a></td> 
<?php
    if ($chapter == 'Auto generated index')
      print("        <td></td>\n");
    else
      print("        <td><a href=\"" . full_url('tex/' . $filename . '.tex') . "\"><code>tex</code></a></td>\n");
?>
        <td><a href="<?php print(full_url('download/' . $filename . '.pdf')); ?>"><code>pdf</code></a></td> 
        <td><a href="<?php print(full_url('download/' . $filename . '.dvi')); ?>"><code>dvi</code></a></td> 
      </tr> 
<?php
  }

  // print a row of the table containing a part
  function print_part($part) {
?>
      <tr> 
        <td><?php print($part); ?></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
<?php
  }

  function print_table() {
?>
    <table id="browse"> 
      <tr> 
        <th>Part</th> 
        <th>Chapter</th> 
        <th>online</th>
        <th>TeX</th> 
        <th>pdf</th> 
        <th>dvi</th> 
      </tr> 
<?php
    global $db;

    // mapping the first chapter of each part to the title of the part
    $parts = array('Introduction' => 'Preliminaries', 'Schemes' => 'Schemes', 'Algebraic Spaces' => 'Algebraic Spaces', 'Stacks' => 'Algebraic Stacks', 'Coding Style' => 'Miscellany');

    try {
      $sql = $db->prepare('SELECT number, title, filename FROM sections WHERE number NOT LIKE "%.%" ORDER BY CAST(number AS INTEGER)');
      if ($sql->execute()) {
        while ($row = $sql->fetch()) {
          // check wheter it's the first chapter, insert row with part if necessary
          if (array_key_exists($row['title'], $parts)) {
            print_part($parts[$row['title']]);
          }

          // change LaTeX escaping to HTML escaping
          print_chapter(latex_to_html($row['title']), $row['filename'], $row['number']);
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
?>
      <tr> 
        <td><a href="<?php print(full_url('bibliography')); ?>">Bibliography</a></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </table>
<?php
  }

?>
<html>
  <head>
    <title>Stacks Project -- Browse</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>Browse chapters</h2>
<?php
  print_table();
?>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
