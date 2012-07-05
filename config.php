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
    implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), -1));
    return $directory;
  }
  
  function full_url($path) {
    return get_directory() . '/' . $path;
  }
?>
