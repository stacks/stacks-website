<?php
  // current test version on paard.math.columbia.edu
  //$directory = '/~pieter/algebraic_geometry/stacks-website/';
  //$domain = 'http://paard.math.columbia.edu:8080'
  // local version
  $directory = '/';
  $domain = 'localhost';

  
  function full_url($path) {
    global $directory;
    return $directory . $path;
  }
?>
