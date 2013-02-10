<?php


class xautoload_ServiceRegistry {

  protected $factory;
  protected $services = array();

  function get($key) {
    if (!isset($this->services[$key])) {
      $this->services[$key] = $this->factory->$key($this);
      if (!isset($this->services[$key])) {
        $this->services[$key] = FALSE;
      }
    }
    return $this->services[$key];
  }

  function reset($key) {
    $this->services[$key] = NULL;
  }

  function __get($key) {
    return $this->get($key);
  }

  function __construct($factory) {
    $this->factory = $factory;
  }
}
