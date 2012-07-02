<!doctype html>
<?php
  error_reporting(E_ALL);

  include('config.php');
  include('functions.php');
  include('php-markdown-extra-math/markdown.php');

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
      $sql = $db->prepare('SELECT id, tag, author, date, comment, site FROM comments WHERE tag = :tag');
      $sql->bindParam(':tag', $tag);

      if ($sql->execute()) {
        while ($row = $sql->fetch()) array_push($comments, $row);
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
      $sql = $db->prepare('SELECT number, title, filename FROM sections WHERE number = :id');
      $sql->bindParam(':id', $id);

      if ($sql->execute()) {
        while ($row = $sql->fetch()) return $row;
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
      $sql = $db->prepare('SELECT tag, label, file, chapter_page, book_page, book_id, value FROM tags WHERE tag = :tag');
      $sql->bindParam(':tag', $tag);

      if ($sql->execute()) {
        // return first (= only) row of the result
        while ($row = $sql->fetch()) return $row;
      }
      return null;
    }
    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }

  function print_comment_input($tag) {
?>
    <h2>Add a comment</h2>
    <p>Your email address will not be published. Required fields are marked.
  
    <p>In your comment you can use Markdown and LaTeX style mathematics (enclose it like <code>$\pi$</code>). A preview option is available if you wish to see how it works out.
  
    <form name="comment" id="comment-form" action="<?php print(full_url('post.php')); ?>" method="post">
      <label for="name">Name<sup>*</sup>:</label>
      <input type="text" name="name" id="name"><br>
  
      <label for="mail">E-mail<sup>*</sup>:</label>
      <input type="text" name="email" id="mail"><br>
  
      <label for="site">Site:</label>
      <input type="text" name="site" id="site"><br>
  
      <label>Comment:</label>
      <textarea name="comment" id="comment-textarea"></textarea>
      <div id="epiceditor"></div>
      <script type='text/javascript'>
        var editor = new EpicEditor(options).load(function() {
            // TODO find out why this must be a callback in the loader, editor.on('load', ...) doesn't seem to be working?!
            // hide textarea, EpicEditor will take over
            document.getElementById('comment-textarea').style.display = 'none';
            // when the form is submitted copy the contents from EpicEditor to textarea
            document.getElementById('comment-form').onsubmit = function() {
              document.getElementById('comment-textarea').value = editor.exportFile();
            };
        });
  
        function preview(iframe) {
          var mathjax = iframe.contentWindow.MathJax;
  
          mathjax.Hub.Config({
            tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
          });
  
          var preview = iframe.contentDocument.getElementById('epiceditor-preview');
          // TODO might it be better to queue this?
          setTimeout(function() { mathjax.Hub.Typeset(preview); }, 500);
        }
  
        editor.on('preview', function() {
            var iframe = editor.getElement('previewerIframe');
  
            if (iframe.contentDocument.getElementById('previewer-mathjax') == null) {
              var script = iframe.contentDocument.createElement('script');
              script.type = 'text/javascript';
              script.src = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_HTML';
              script.setAttribute('id', 'previewer-mathjax');
              iframe.contentDocument.head.appendChild(script);
            }
  
            // wait a little for MathJax to initialize
            // TODO might this be possible through a callback?
            if (iframe.contentWindow.MathJax == null) {
              setTimeout(function() { preview(iframe) }, 500);
            }
            else {
              preview(iframe);
            };
        });
      </script>
  
      <input type="hidden" name="tag" value="<?php print($tag); ?>">
  
      <input type="submit" id="comment-submit" value="Post comment">
    </form>
<?php
  }

  function print_comments($tag) {
    print("    <h2>Comments</h2>\n");

    $comments = get_comments($tag);
    if (count($comments) == 0) {
      print("    <p>There are no comments yet for this tag.</p>\n\n");
    }
    else {
      foreach ($comments as $comment) {
        print("    <div class='comment'>\n");
        print("      <a name='comment-" . $comment['id'] . "'></a>\n");
        // TODO htmlentities
        print("      Comment by <cite class='comment-author'>" . $comment['author'] . "</cite>");
        if (!empty($comment['site'])) {
          print(" (<a href='" . $comment['site'] . "'>site</a>)\n");
        }
        $date = date_create($comment['date'], timezone_open('GMT'));
        print("      <span class='comment-date'><a href='#comment-" . $comment['id'] . "'>" . date_format($date, 'F j, Y \a\t g:i a e') . "</a></span>\n");
        print("      <blockquote>" . str_replace("\xA0", ' ', Markdown($comment['comment'])) . "</blockquote>\n");
        print("    </div>\n\n");
      }
    }
  }

  function print_tag($tag) {
    $results = get_tag($tag);
    
    print("    <h2>Tag: <var>" . $tag . "</var></h2>\n");

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
    print("      <li><a href='" . $information['filename'] . ".pdf#" . $tag . "'>Lemma " . $relative_id . " on page " . $results['chapter_page'] . "</a> of Chapter " . $chapter_id . ": " . $information['title'] . "\n");
    print("      <li><a href='book.pdf#" . $tag . "'>Lemma " . $results['book_id'] . " on page " . $results['book_page'] . "</a> of the book version\n");
    print("    </ul>\n\n");
    print("    The LaTeX code of the corresponding environment is:\n");
    print("    <pre>\n" . $results['value'] . "\n    </pre>\n");
  }

  function print_missing_tag($tag) {
    print("    <h2>Missing tag: <var>" . $tag . "</var></h2>\n");
    print("    <p>The tag you requested does not exist.\n");
  }
?>
<html>
  <head>
    <title>Stacks Project -- Tag lookup</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 

    <script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <script type="text/x-mathjax-config">
      MathJax.Hub.Config({
        tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
      });
    </script>

    <script type="text/javascript" src="<?php print(full_url('EpicEditor/epiceditor/js/epiceditor.js')); ?>"></script>
    <script type="text/javascript">
      var options = {
        basePath: '<?php print(full_url('EpicEditor/epiceditor')); ?>',
        file: {
          name: '<?php print(htmlspecialchars($_GET['tag'])); ?>',
        },
      }
    </script>
  </head>
  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>

    <h2>Look for a tag</h2>

    <form action="<?php print(full_url('search.php')); ?>" method="post">
      <label>Tag: <input type="text" name="tag"></label>
      <input type="submit" value="locate">
    </form>

    <p>For more information we refer to the <a href="#">tags explained</a> page.

<?php
  if (!empty($_GET['tag'])) {
    $_GET['tag'] = strtoupper($_GET['tag']);

    if (is_valid_tag($_GET['tag'])) {
      // from here on it's safe to ignore the fact it is user input
      $tag = $_GET['tag'];

      if (tag_exists($tag)) {
        print_tag($tag);
        print_comments($tag);

        print_comment_input($tag);
      }
      else {
        print_missing_tag($tag);
      }
    }
    else {
      print("    <h2>Error</h2>\n");
      print("    The tag you provided (i.e. <var>" . $_GET['tag'] . "</var>) is not in the correct format.\n");
    }
  }
?>
    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
