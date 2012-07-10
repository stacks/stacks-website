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

  // get all active tags from a given chapter
  function get_tags($chapter_id) {
    global $db;
    $tags = array();

    try {
      $sql = $db->prepare("SELECT tag, label, book_id, type, book_page, file FROM tags WHERE active = 'TRUE' AND book_id LIKE '" . $chapter_id . ".%' ORDER BY position");
      
      if ($sql->execute()) {
        // add all rows to an array
        while ($row = $sql->fetch())
          $tags[] = $row;
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    return $tags;
  }

  function print_tag($tag) {
    print("<li><a href='" . full_url('tag/' . $tag['tag']) . "'>Tag <var>" . $tag['tag'] . "</var></a> references <a title='" . $tag['label'] . "' href='" . full_url('tex/' . $tag['file'] . '.pdf#' . $tag['tag']) . "'>" . ucfirst($tag['type']) . " " . $tag['book_id'] . "</a>\n");
  }

  function print_tags($chapter_id) {
    $tags = get_tags($chapter_id);

    // start global list
    print("<ul>");

    // keep track of how many <ul>'s where issued
    $depth = 0;
    // are we printing a list of equations?
    $equation_mode = false;
    // have we just issued a new sub(section)?
    $section_issued = false;
    
    foreach ($tags as $tag) {
      // we have finished a list of equations not directly contained in a (sub)section
      if ($tag['type'] != 'equation' and !$section_issued and $equation_mode) {
        $equation_mode = false;
        print("</ul>");
        $depth--;
      }

      // just issued a section, if an equation occurs immediately after this do not start a new list
      if ($tag['type'] == 'section' or $tag['type'] == 'subsection')
        $section_issued = true;

      switch ($tag['type']) {
        case 'section':
          // do not close the container <ul>
          print(str_repeat("</ul>\n", $depth - 1));
          $depth = 2;
          print_tag($tag);
          print("<ul>");
          break;

        case 'subsection':
          // subsections mustn't close the section, therefore -2
          print(str_repeat("</ul>\n", $depth - 2));
          $depth = 3;
          print_tag($tag);
          print("<ul>");
          break;

        case 'equation':
          // start new list because the equations belong to something like a Lemma
          if (!$section_issued and !$equation_mode) {
            print("<ul>");
            $depth++;
          }
          print_tag($tag);
          break;

        default:
          $section_issued = false;
          print_tag($tag);
          break;
      }

      // let it be known whether we just issued an equation or not
      $equation_mode = ($tag['type'] == 'equation');
    }

    // end all pending lists, 
    print(str_repeat("</ul>", $depth));
  }
?>
<html>
  <head>
    <title>Stacks Project -- Overview</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
<?php
  print_r(print_tags('56'));
?>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
