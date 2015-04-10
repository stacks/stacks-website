<?php

function getSimpleSearchForm($autofocus = true, $size = 20) {
  $value = "";
  $value .= "<form class='simple' action='" . href("search") . "' method='get'>"; 
  $value .= "<label><span>Keywords:</span> <input type='text' name='keywords' " . ($autofocus ? "autofocus" : "") . " size='" . $size . "'></label>";
  $value .= "<input type='submit' value='search'>";
  $value .= "</form>";

  return $value;
}

?>
