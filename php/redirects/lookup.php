<?php
  include("../general.php");
  
  header("Location: " . href("tag/") . $_POST["tag"]);
?>
