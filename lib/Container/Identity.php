<?php

class xautoload_Container_Identity {

  /**
   * @var mixed
   */
  protected $arg;

  /**
   * @param mixed $arg
   */
  function __construct($arg) {
    $this->arg = $arg;
  }

  /**
   * @return mixed
   */
  function get() {
    return $this->arg;
  }
}
