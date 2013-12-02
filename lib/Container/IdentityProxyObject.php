<?php

class xautoload_Container_IdentityProxyObject extends xautoload_Container_ProxyObject {

  /**
   * @var object
   */
  protected $identityInstance;

  /**
   * @param object $instance
   */
  function __construct($instance) {
    $this->identityInstance = $instance;
  }

  /**
   * @return object
   */
  protected function proxyCreateInstance() {
    return $this->identityInstance;
  }
}
