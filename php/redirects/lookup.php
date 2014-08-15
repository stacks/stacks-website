<?php
  $config = parse_ini_file("../../config.ini");

  require_once("../general.php");
  header("Location: " . (isset($_POST['HTTP_ORIGIN']) ? $_POST['HTTP_ORIGIN'] : '') . href("tag/" . strtoupper(isset($_POST["tag"]) ? $_POST["tag"] : '')));
?>
