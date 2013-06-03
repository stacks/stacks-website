<?php

function getSimpleSearchForm($size = 20) {
  $value = "";
  $value .= "<form action='#' method='get'>";
  $value .= "<label>Keywords: <input type='text' name='keywords' size='" . $size . "'></label>"; // TODO find good length for this input box that works everywhere, or via parameter
  $value .= "<input type='submit' value='search'>";
  $value .= "</form>";

  return $value;
}

?>
