<?php
header('Content-type: text/plain');

$output = '';
exec('/usr/local/bin/git submodule foreach git log --stat -50', $output);

$tex_project = false;
foreach ($output as $line) {
  if (substr($line, 0, 9) == 'Entering ') {
    $tex_project = ($line == 'Entering \'tex\'');
    continue;
  }

  if ($tex_project)
    print($line . "\n");
}
?>
