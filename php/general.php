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


// this is minor TeX parsing routine, mostly used for interpreting brackets {} in the correct way depending on math mode or not
function parseTeX($value) {
  $value = parseAccents($value);

  $value = preg_replace("/\\\url\{(.*)\}/", '<a href="$1">$1</a>', $value);
  $value = preg_replace("/\{\\\itshape(.*)\}/", '$1', $value);
  $value = str_replace("\\bf ", '', $value);

  $parts = explode('$', $value);
  for ($i = 0; $i < count($parts); $i++) {
    // not in math mode, i.e. remove all {}
    if ($i % 2 == 0) {
      $parts[$i] = str_replace('{', '', $parts[$i]);
      $parts[$i] = str_replace('}', '', $parts[$i]);
    }
  }
  $value = implode('$', $parts);

  $value = str_replace("--", "&ndash;", $value);

  return $value;
}



?>
