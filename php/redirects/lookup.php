<?php
  $config = parse_ini_file("../../config.ini");

  require_once("../general.php");
  header("Location: " . $_SERVER["HTTP_ORIGIN"] . href("tag/" . $_POST["tag"]));
?>
