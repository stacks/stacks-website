<?php
  include('config.php');
  
  header('Location: ' . full_url('tag/') . $_POST['tag']);
?>
