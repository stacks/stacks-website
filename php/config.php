<?php
  // some configuration which should not be hidden in the code
  // only non-specific configuration should be used here (use config.ini for specific configuration)
  $config = array(
    "jQuery" => "https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js",
    "blog" => "http://math.columbia.edu/~dejong/wordpress/",
    "blog feed" => "http://math.columbia.edu/~dejong/wordpress/?feed=rss2",
    "GitHub feed" => "https://github.com/stacks/stacks-project/commits/master.atom",
    "SimplePie cache" => $_SERVER["DOCUMENT_ROOT"] . "/php/cache",
  );
?>
