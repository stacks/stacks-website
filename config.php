<?php
  // actual domain in use (cannot be deduced from $_SERVER)
  $domain = 'http://math.columbia.edu';


  // place the database in a directory that is not visible from outside
  $database_directory = '/home/belmans/stacks';
  $database_name = 'stacks.sqlite';

  function get_database_location() {
    global $database_directory, $database_name;
    return 'sqlite:' . $database_directory . '/' . $database_name;
  }

  function get_directory() {
    return '/~belmans/algebraic_geometry/stacks_website';
    // found out it never worked because of the stange - _ mess, so ignore this for now, if the site is running in the same directory it is accessed from this works
    //return implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
  }
  
  function full_url($path) {
    return get_directory() . '/' . $path; 
  }
?>
