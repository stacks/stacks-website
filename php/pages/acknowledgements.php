<?php

require_once("php/page.php");
require_once("php/general.php");

class AcknowledgementsPage extends Page {
  public function getMain() {
    global $config;
    $output = "";

    $output .= "<h2>Acknowledgements</h2>";
    $output .= "<p>What you see here is the current status of the file <a href='https://github.com/stacks/stacks-project/tree/master/documentation'><var>documentation/support</var></a> in the project. The intent is to list support from institutions that have made the Stacks project possible. If you have participated in the Stacks project and wish to acknowledge support, please contact <a href='mailto:stacks.project@gmail.com'>stacks.project@gmail.com</a> and we will add it here.";
    $file = file_get_contents($config["project"] . "/documentation/support");
    $items = explode("\n\n\n", $file);
    $output .= "<ol>";
    foreach ($items as $item) {
      $item = str_replace('<em>', '_', $item);
      $item = str_replace('</em>', '_', $item);
      $item = str_replace('<p>', '', $item);
      $item = str_replace('</p>', '', $item);
      $output .= "<li>" . $item;
    }
    $output .= "</ol>";

    return $output;
  }
  public function getSidebar() {
    $output = "";
    
    // TODO what to do in the sidebar?

    return $output;
  }
  public function getTitle() {
    return " &mdash; Acknowledgements";
    // TODO fix this in all the other pages: add title and use &mdash;
  }
}

?>


