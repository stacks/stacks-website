<?php
  include('config.php');
  
  // TODO fix mod_rewrite
  header('Location: ' . full_url('tag/') . $_POST['tag']);
?>
