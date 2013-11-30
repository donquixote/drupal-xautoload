<?php

class xautoload_Container_MagicGet {

  /**
   * @var object
   */
  protected $container;

  /**
   * @var mixed
   */
  protected $key;

  /**
   * @param object $container
   *   An object with a __get() method.
   * @param string $key
   *   Key to be passed to the __get() method as an argument.
   */
  function __construct($container, $key) {
    $this->container = $container;
    $this->key = $key;
  }

  /**
   * @return mixed
   */
  function get() {
    return $this->container->__get($this->key);
  }
}
