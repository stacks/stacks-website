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
<?php
    if ($chapter == 'Bibliography')
      print("        <td><a href='" . full_url('bibliography') . "'><code>online</code></a></td>");
    else
      print("        <td><a href='" . full_url('chapter/' . $number) . "'><code>online</code></a></td>");

    if ($chapter == 'Auto generated index')
      print("        <td></td>\n");
    elseif ($chapter == 'Bibliography')
      print("        <td><a href=\"" . full_url('tex/my.bib') . "\"><code>tex</code></a></td>\n");
    else
      print("        <td><a href=\"" . full_url('tex/' . $filename . '.tex') . "\"><code>tex</code></a></td>\n");

    if ($chapter == 'Bibliography') {
?>
        <td><a href="<?php print(full_url('download/bibliography.pdf')); ?>"><code>pdf</code></a></td> 
        <td><a href="<?php print(full_url('download/bibliography.dvi')); ?>"><code>dvi</code></a></td> 
<?php
    }
    else {
?>
        <td><a href="<?php print(full_url('download/' . $filename . '.pdf')); ?>"><code>pdf</code></a></td> 
        <td><a href="<?php print(full_url('download/' . $filename . '.dvi')); ?>"><code>dvi</code></a></td> 
<?php
    }
?>
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
    $parts = array(
      'Introduction' => 'Preliminaries',
      'Schemes' => 'Schemes',
      'Chow Homology and Chern Classes' => 'Topics in Scheme Theory',
      'Algebraic Spaces' => 'Algebraic Spaces',
      'Formal Deformation Theory' => 'Deformation Theory',
      'Algebraic Stacks' => 'Algebraic Stacks',
      'Examples' => 'Miscellany');
    $number = 0;

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
          $number = $row['number'];
        }
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    // print bibliography
    print_chapter('Bibliography', '', $number + 1);
?>
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
