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
      $query = 'SELECT tags.tag, tags.label, tags.type, tags.book_id, tags_search.text, tags_search.text_without_proofs FROM tags_search, tags WHERE tags_search.tag = tags.tag AND tags.active = "TRUE"';
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

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>

    <style type="text/css">
      p.preview {
        margin: .4em 0;
      }
    </style>

    <script type="text/javascript">
  $(document).ready(function() {
    // insert collapse / expand all links
    $("#results").before('<a href="javascript:void(0)" onclick="$(\'#results pre\').hide();"> <img src="<?php print(full_url('jquery-treeview/images/minus.gif')); ?>"> Collapse all</a>');
    $("#results").before(' <a href="javascript:void(0)" onclick="$(\'#results pre\').show();"><img src="<?php print(full_url('jquery-treeview/images/plus.gif')); ?>"> Expand all</a>');

    // insert toggle links for each result
    pre = $("#results pre");
    for (var i = 0; i < pre.length; i++) {
      el = $(pre[i]);
      el.before('<p class="preview"><a href="javascript:void(0)" onclick="$(\'#' + pre[i].id + '\').toggle();">preview tag</a></p>');
    }

    // hide all results by default
    pre.hide();
  });
    </script>
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

      <label></label>
      <input type="submit" id="submit" value="Search">
    </form>
    <p>The easy version: you can search just like you would in Google, a search query like <var>divisor "separated scheme"</var> matches all tags containing <em>both</em> the word <var>divisor</var> and the string <var>separated scheme</var>. Some remarks:
    <ul>
      <li>strings like <var>quasi-compact</var> <em>should be enclosed by quotes</em>, otherwise you are looking for tags that contain the string <var>quasi</var> but not <var>compact</var>
      <li>tags can also refer to complete sections, you can choose whether to include these: including them will duplicate some results, but might give the information you need
      <li>it's also possible to include proofs in your search, by default these are excluded, and depending on your choice the proofs will be shown in the preview or not
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
      print("<ul id='results'>");
      foreach ($results as $result) {
        print("<li><p><a href='" . full_url('tag/' . $result['tag']) . "'>Tag <code>" . $result['tag'] . "</code></a> which points to " . ucfirst($result['type']) . " " . $result['book_id'] . " matched your query.\n");
        if ($include_proofs)
          print("<pre id='text-" . $result['tag'] . "'>" . $result['text'] . "</pre>");
        else
          print("<pre id='text-" . $result['tag'] . "'>" . $result['text_without_proofs'] . "</pre>");
      }
      print("</ul>");
    }
  }
?>
    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>

