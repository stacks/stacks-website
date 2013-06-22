<?php
  include("../general.php");
  header("Location: " . $_SERVER["HTTP_REFERER"] . "/" . $_POST["tag"]);
?>
