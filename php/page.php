<?php

/**
 * The KISS approach to a template engine
 *
 * To get a certain level of abstraction each (class of) page(s) implements
 * this abstract class, which contains certain hooks for all the required
 * page elements.
 */
abstract class Page {
  public function getHead() {
    return "";
  }
  abstract public function getMain();
  abstract public function getSidebar();
  abstract public function getTitle();
}

?>
