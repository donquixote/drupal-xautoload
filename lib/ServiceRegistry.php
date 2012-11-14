<?php


class xautoload_ServiceRegistry {

  protected $factory;
  protected $cache = array();

  function get($key) {
    if (!isset($this->cache[$key])) {
      $this->cache[$key] = $this->factory->$key($this);
      if (!isset($this->cache[$key])) {
        $this->cache[$key] = FALSE;
      }
    }
    return $this->cache[$key];
  }

  function reset($key) {
    $this->cache[$key] = NULL;
  }

  function __get($key) {
    return $this->get($key);
  }

  function __construct($factory) {
    $this->factory = $factory;
  }
}
