<?php

abstract class xautoload_Container_ProxyObject {

  /**
   * @var array
   */
  protected $observers = array();

  /**
   * @var array
   */
  protected $scheduled = array();

  /**
   * @var object
   */
  protected $instance;

  /**
   * @param callable $callback
   */
  function proxyObserveInstantiation($callback) {
    if (!isset($this->instance)) {
      $this->observers[] = $callback;
    }
    else {
      call_user_func($callback, $this->instance);
    }
  }

  /**
   * @return mixed
   */
  function proxyGetInstance() {
    if (!isset($this->instance)) {
      $this->instance = $this->proxyCreateInstance();
      foreach ($this->observers as $callback) {
        call_user_func($callback, $this->instance);
      }
      foreach ($this->scheduled as $info) {
        list($method, $args) = $info;
        call_user_func_array(array($this->instance, $method), $args);
      }
    }
    return $this->instance;
  }

  /**
   * @param string $method
   * @param array $args
   */
  function proxyScheduleOperation($method, $args = array()) {
    if (!isset($this->instance)) {
      $this->scheduled[] = array($method, $args);
    }
    else {
      call_user_func_array(array($this->instance, $method), $args);
    }
  }

  /**
   * @return mixed
   */
  abstract protected function proxyCreateInstance();
}
