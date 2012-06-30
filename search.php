<?php
  include('config.php');
  
  // TODO fix mod_rewrite
  header('Location: ' . $directory . 'tag/' . $_POST['tag']);
?>
