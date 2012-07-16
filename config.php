<?php
  // actual domain in use (cannot be deduced from $_SERVER)
  $domain = 'localhost';


  // place the database in a directory that is not visible from outside
  $database_directory = '';
  $database_name = 'stacks.sqlite';

  function get_database_location() {
    global $database_directory, $database_name;
    return 'sqlite:' . $database_directory . $database_name;
  }

  function get_directory() {
    return implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
  }
  
  function full_url($path) {
    return get_directory() . '/' . $path; 
  }
?>
