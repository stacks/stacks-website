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

  function search($keywords, $exclude_sections, $include_proofs) {
    global $db;

    $results = array();

    try {
      // FTS queries don't work with PDO (or maybe: a) I didn't try hard enough, b) did something stupid)
      $query = 'SELECT tags.tag, tags.label, tags.type, tags.book_id FROM tags_search, tags WHERE tags_search.tag = tags.tag AND tags.active = "TRUE"';
      // the user doesn't want tags of the type section or subsection (which contain all the tags from that section)
      if ($exclude_sections) 
        $query .= ' AND tags.type NOT IN ("section", "subsection")';

      // the user wants to include the proofs in his query
      if ($include_proofs)
        $query .= ' AND tags_search.text MATCH ' . $db->quote($keywords);
      else
        $query .= ' AND tags_search.text_without_proofs MATCH ' . $db->quote($keywords);

      // TODO order by rank
      $query .= " ORDER BY tags.position";

      foreach ($db->query($query) as $row)
        $results[] = $row;
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }

    return $results; 
  }
?>
<html>
  <head>
    <title>Stacks Project -- Search</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>Search</h2>
    <form id="search" action="<?php print(full_url('search')); ?>" method="get">
      <label for="keywords">Keywords:</label>
      <input type="text" id="keywords" name="keywords" value="<?php if(isset($_GET['keywords'])) print(htmlentities($_GET['keywords'])); ?>"><br>

      <label for="include-sections">Include sections:</label>
      <input type="checkbox" <?php if(isset($_GET['include-sections']) && $_GET['include-sections'] == 'on') print('checked="true"'); ?> id="include-sections" name="include-sections"><br>

      <label for="include-proofs">Include proofs:</label>
      <input type="checkbox" <?php if(isset($_GET['include-proofs']) && $_GET['include-proofs'] == 'on') print('checked="true"'); ?> id="include-proofs" name="include-proofs"><br>

      <input type="submit" value="locate">
    </form>
    <p>The easy version: you can search just like you would in Google, a search query like <var>divisor "separated scheme"</var> matches all tags containing <em>both</em> the word <var>divisor</var> and the string <var>separated scheme</var>. Some remarks:
    <ul>
      <li>strings like <var>quasi-compact</var> <em>should be enclosed by quotes</em>, otherwise you are looking for tags that contain the string <var>quasi</var> but not <var>compact</var>
      <li>tags also refer to complete sections, we don't show these in the 
    </ul>

    <p>The full version: the search functionality is provided by <a href="http://www.sqlite.org/fts3.html">SQLite's FTS extension</a>. You can use all the features described there.</p>

<?php
  if (isset($_GET['keywords'])) {
    print("<h2>Search results</h2>");

    $exclude_sections = !(isset($_GET['include-sections']) and $_GET['include-sections'] = 'on');
    $include_proofs = isset($_GET['include-proofs']) and $_GET['include-proofs'] = 'on';
    $results = search($_GET['keywords'], $exclude_sections, $include_proofs);
    print("<p>Your search for <var>" . htmlentities($_GET['keywords']) . "</var> returned " . count($results) . " hits.");

    if (count($results) > 0) {
      print("<ul>");
      foreach ($results as $result) {
        print("<li><p><a href='" . full_url('tag/' . $result['tag']) . "'>Tag <code>" . $result['tag'] . "</code></a> which points to " . ucfirst($result['type']) . " " . $result['book_id'] . " matched your query.");
      }
      print("</ul>");
    }
  }
?>
    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>

