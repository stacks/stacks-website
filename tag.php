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
    print("    <h2>Tag: " . $tag . "</h2>\n");
    print("    The tag " . $tag . " references\n");
    print("    <ul>\n");
    print("      <li><a href='#'>Lemma 9.6 on page 8</a> of Chapter 5: Topology\n");
    print("      <li><a href='#'>Lemma 5.9.6 on page 140</a> of the entire book\n");
    print("    </ul>\n\n");
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

    For more information we refer to the <a href="#">tags explained</a> page.

<?php
  if (!empty($_GET['tag'])) {
    if (is_valid_tag($_GET['tag'])) {
      print_tag($_GET['tag']);
      print_comments($_GET['tag']);
    }
    else {
      print("    <h2>Error</h2>\n");
      print("    The tag you provided is not in the correct format.\n");
    }
  }
?>
  </body>
</html>
