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
    
    print("    <h2>Tag: <tt>" . $tag . "</tt></h2>\n");
    if (is_null($results)) {
      print("    <p>This tag has not been found in the Stacks Project.\n");
    }
    else {
      print("    <p>This tag has label <tt>" . $results['label'] . "</tt> and it references\n");
      print("    <ul>\n");
      print("      <li><a href='#'>Lemma " . implode('.', array_slice(explode('.', $results['book_id']), 1)) . " on page " . $results['book_page'] . "</a> of TODO\n");
      print("      <li><a href='#'>Lemma " . $results['book_id'] . " on page " . $results['book_page'] . "</a> of the entire book\n");
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
    }
    else {
      print("    <h2>Error</h2>\n");
      print("    The tag you provided (i.e. <tt>" . $_GET['tag'] . "</tt>) is not in the correct format.\n");
    }
  }
?>
  </body>
</html>
