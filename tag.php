<!doctype html>
<?php
  include('functions.php');

  try {
    $db = new PDO('sqlite:stacks.sqlite');
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }

  function get_comments($tag) {
    assert(is_valid_tag($tag));

    global $db;
    $comments = array();
    try {
      $sql = 'SELECT id, tag, author, date, comment, site FROM comments WHERE tag = "' . $tag . '"';
      foreach ($db->query($sql) as $row) {
        array_push($comments, $row);
      }
    }
    catch(PDOException $e) {
      echo $e->getMessage();

      return array();
    }

    return $comments;
  }

  function get_section($id) {
    global $db;

    try {
      $sql = 'SELECT number, title, filename FROM sections WHERE number = "' . $id . '"';
      foreach ($db->query($sql) as $row) {
        return $row;
      }
      # TODO error handling
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }

  function get_tag($tag) {
    assert(is_valid_tag($tag));

    global $db;
    try {
      $sql = 'SELECT tag, label, file, chapter_page, book_page, book_id, value FROM tags WHERE tag = "' . $tag . '"';
      $result = $db->query($sql);

      // return first (= only) row of the result
      foreach ($result as $row) {
        return $row;
      }
      // no rows found
      return null;
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }

  function print_comment_input() {
    print("    <div id='epiceditor'></div>\n");
    print("    <script type='text/javascript'>\n");
    print("      var editor = new EpicEditor(options).load();\n");
    print("    </script>\n");
  }

  function print_comments($tag) {
    print("    <h2>Comments</h2>\n");

    $comments = get_comments($tag);
    if (count($comments) == 0) {
      print("    There are no comments yet for this tag.\n");
    }
    else {
      foreach ($comments as $comment) {
        print("    <cite class='comment-author'>" . $comment['author'] . "</cite>");
        print(" (<a href='" . $comment['site'] . "'>website</a>)\n");
        print("    <span class='comment-date'>" . $comment['date'] . "</span>\n");
        print("    <blockquote>" . $comment['comment'] . "</blockquote>\n\n");
      }
    }
  }

  function print_tag($tag) {
    $results = get_tag($tag);
    
    print("    <h2>Tag: <var>" . $tag . "</var></h2>\n");
    if (is_null($results)) {
      print("    <p>This tag has not been found in the Stacks Project.\n");
    }
    else {
      $parts = explode('.', $results['book_id']);
      # the identification of the result relative to the local section
      $relative_id = implode('.', array_slice($parts, 1));
      # the identification of the (sub)section of the result
      # TODO tags can be entire chapters, right? i.e. problem
      $section_id = implode('.', array_slice($parts, 0, -1));
      # the id of the chapter, the first part of the full identification
      $chapter_id = $parts[0];
      # all information about the current section TODO better naming might be appropriate
      $information = get_section($section_id);

      print("    <p>This tag has label <var>" . $results['label'] . "</var> and it references\n");
      print("    <ul>\n");
      print("      <li><a href='" . $information['filename'] . ".pdf#" . $tag . "'>Lemma " . $relative_id . " on page " . $results['book_page'] . "</a> of Chapter " . $chapter_id . ": " . $information['title'] . "\n");
      print("      <li><a href='book.pdf#" . $tag . "'>Lemma " . $results['book_id'] . " on page " . $results['book_page'] . "</a> of the book version\n");
      print("    </ul>\n\n");
      print("    The LaTeX code of the corresponding environment is:\n");
      print("    <pre>\n" . $results['value'] . "\n    </pre>\n");
    }
  }
?>
<html>
  <head>
    <title>Stacks Project -- Tag lookup</title>
    <link rel="stylesheet" type="text/css" href="/style.css">

    <script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <script type="text/x-mathjax-config">
      MathJax.Hub.Config({
        tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
      });
    </script>

    <!-- TODO fix relative URL -->
    <script type="text/javascript" src="/EpicEditor/epiceditor/js/epiceditor.js"></script>
    <script type="text/javascript">
      var options = {
        basePath: '/EpicEditor/epiceditor',
      }
    </script>
  </head>
  <body>
    <h1>The Stacks Project</h1>

    <h2>Look for a tag</h2>

    <form action="/search.php" method="post">
      <label>Tag: <input type="text" name="tag"></label>
      <input type="submit" value="locate">
    </form>

    <p>For more information we refer to the <a href="#">tags explained</a> page.

<?php
  if (!empty($_GET['tag'])) {
    if (is_valid_tag($_GET['tag'])) {
      print_tag($_GET['tag']);
      print_comments($_GET['tag']);

      print_comment_input();
    }
    else {
      print("    <h2>Error</h2>\n");
      print("    The tag you provided (i.e. <var>" . $_GET['tag'] . "</var>) is not in the correct format.\n");
    }
  }
?>
  </body>
</html>
