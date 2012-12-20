<?php
  header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<?php
  error_reporting(E_ALL);

  include('config.php');
  include('functions.php');

  // initialize the global database object
  try {
    $db = new PDO(get_database_location());
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
?>
<html>
  <head>
<?php
  if (isset($_GET['name']))
    print("    <title>Stacks Project -- Bibliography item " . htmlentities($_GET['name']) . "</title>\n");
  else
    print("    <title>Stacks Project -- Tag lookup</title>\n");
?>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 

    <script type="text/javascript" src="/MathJax/MathJax.js?config=default"></script>
    <script type="text/x-mathjax-config">
      MathJax.Hub.Config({
        extensions: ["tex2jax.js", "fp.js"],
        tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]},
        TeX: {extensions: ["xypic.js", "AMSmath.js", "AMSsymbols.js"], TagSide: "left"},
        "HTML-CSS": { scale: 85 }
      });
    </script>

    <meta charset="utf-8">
  </head>
  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

<?php
  if (isset($_GET['name'])) {
    if (bibliography_item_exists($_GET['name'])) {
      print("<h2>Bibliography item: <code>" . $_GET['name'] . "</code></h2>");

      $item = get_bibliography_item($_GET['name']);

      print("<dl>");
      foreach ($item as $key => $value) {
        print("<dt>" . $key . "</dt><dd>" . latex_to_html($value) . "</dd>");
      }
      print("</dl>");
    }
    else {
      print("<h2>Error</h2>");
      print("<p>The name of the bibliography you are looking for (i.e. <var>" . htmlentities($_GET['name']) . "</var>) does not exist. You can try the overview at the <a href='" . full_url('bibliography') . "'>bibliography page</a>\n");
    }
  }
  else {
    $items = get_bibliography_items();
    print("<ul>");
    foreach ($items as $name => $item) {
      // TODO move this to function and make a way better implementation
      print("<li>" . latex_to_html($item['author']) . ", <a href='" . full_url('bibliography/' . $name) . "'>" . latex_to_html($item['title']) . '</a>');
      print("<!--\n");
      print_r($item);
      print("\n-->");
    }
    print("</ul>");
  }
?>

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>

