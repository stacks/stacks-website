<?php
  // actual domain in use (cannot be deduced from $_SERVER)
  $domain = 'http://math.columbia.edu';


  // place the database in a directory that is not visible from outside
  $database_directory = '/home/belmans/stacks/';
  $database_name = 'stacks.sqlite';

  function get_database_location() {
    global $database_directory, $database_name;
    return 'sqlite:' . $database_directory . $database_name;
  }

  function get_directory() {
    return implode('/', array_slice(explode('/', $_SERVER['REDIRECT_URL']), 0, -1));
  }
  
  function full_url($path) {
    return get_directory() . '/' . $path; 
  }
?>
