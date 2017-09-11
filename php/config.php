<?php
  // some configuration which should not be hidden in the code
  // only non-specific configuration should be used here (use config.ini for specific configuration)
  $config = array(
    "jQuery" => "https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js",
    "MathJax" => "https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js",
    "D3" => "https://d3js.org/d3.v3.min.js",
    "blog" => "http://math.columbia.edu/~dejong/wordpress/",
    "blog feed" => "http://math.columbia.edu/~dejong/wordpress/?feed=rss2",
    "GitHub feed" => "https://github.com/stacks/stacks-project/commits/master.atom",
    "SimplePie cache" => $_SERVER["DOCUMENT_ROOT"] . "/php/cache",
  );
?>
