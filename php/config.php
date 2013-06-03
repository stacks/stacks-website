<?php
  // some configuration which should not be hidden in the code
  // no values necessary for the actual setup of the website should be used here
  $config = array(
      "project" => "../stacks-project",
      "jQuery" => "https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js",
      "blog" => "http://math.columbia.edu/~dejong/wordpress/",
      "blog feed" => "http://math.columbia.edu/~dejong/wordpress/?feed=rss2",
      "GitHub feed" => "https://github.com/stacks/stacks-project/commits/master.atom",
      "SimplePie cache" => $_SERVER["DOCUMENT_ROOT"] . "/new/php/cache",
  );
?>
