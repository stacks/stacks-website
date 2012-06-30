<?php
include('config.php');

// TODO fix mod_rewrite
header('Location: ' . $directory . 'tag.php?tag=' . $_POST['tag']);
?>
