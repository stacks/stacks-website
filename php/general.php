<?php

// TODO use this variable everywhere
$jQuery = "https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js";

function href($path) {
  // TODO this function should produce correct paths in any situation, given a path relative to the index
  return "/new/" . $path;
}

function parseAccents($text) {
  $text = preg_replace("/\{?\\\'\{?a\}?/", "&aacute;", $text);
  $text = preg_replace("/\{?\\\`\{?a\}?/", "&agrave;", $text);
  $text = preg_replace("/\{?\\\\\"\{?a\}?/", "&auml;", $text);
  $text = preg_replace("/\{\\\\u\{a\}\}?/", "&#259;", $text);
  $text = preg_replace("/\{\\\\v\{a\}\}?/", "&#462;", $text);

  $text = preg_replace("/\{?\\\c\{?c\}?\}?/", '&ccedil;', $text);

  $text = preg_replace("/\{?\\\'\{?E\}?/", "&Eacute;", $text);
  $text = preg_replace("/\{\\\'e\}|\\\'e?/", "&eacute;", $text);
  $text = preg_replace("/\{?\\\`\{?e\}?/", "&egrave;", $text);
  $text = preg_replace("/\{?\\\`\{?E\}?/", "&Egrave;", $text);
  $text = preg_replace("/\{?\\\\\^\{?e\}?/", "&ecirc;", $text);
  $text = preg_replace("/\{?\\\\\"\{?e\}?/", "&euml;", $text);

  $text = preg_replace("/\{?\\\c\{?t\}?\}?/", '&tcedil;', $text);

  $text = preg_replace("/\{?\\\\\"\{?o\}?/", "&ouml;", $text);

  $text = preg_replace("/\{?\\\\\"\{?u\}?/", "&uuml;", $text);
  $text = preg_replace("/\{?\\\`\{?u\}?/", "&ugrave;", $text);

  $text = str_replace("{\\v C}", "&#268;", $text);
  $text = str_replace("\\u C", "&#268;", $text);
  $text = str_replace("``", "''", $text);
  $text = str_replace("''", "''", $text);
  $text = str_replace("\\ ", "&nbsp;", $text);
  // this is to remedy a bug in import_titles
  $text = str_replace("\\v C}", "&#268;", $text);

  return $text;
}


?>
