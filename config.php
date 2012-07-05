<?php
  // current test version on paard.math.columbia.edu
  //$directory = '/~pieter/algebraic_geometry/stacks-website/';
  //$domain = 'http://paard.math.columbia.edu:8080'
  // local version
  $directory = '/';
  $domain = 'localhost';

  // place the database in a directory that is not visible from outside
  $database_directory = '../';
  $database_name = 'stacks.sqlite';

  function get_database_location() {
    global $database_directory, $database_name;
    return 'sqlite:' . $database_directory . $database_name;
  }

  function get_directory() {
    $directory = $_SERVER['SCRIPT_NAME'];
    explode('/', $directory);
    $directory = implode('/', array_splice($directory, -1));
  }
  
  function full_url($path) {
    return get_directory() . '/' . $path;
  }
?>
