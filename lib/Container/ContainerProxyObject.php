<?php

class xautoload_Container_ContainerProxyObject extends xautoload_Container_ProxyObject {

  /**
   * @var xautoload_Container_LazyServices
   */
  protected $container;

  /**
   * @var string
   */
  protected $key;

  /**
   * @param xautoload_Container_LazyServices $container
   * @param string $key
   */
  function __construct($container, $key) {
    $this->container = $container;
    $this->key = $key;
  }

  /**
   * @return mixed
   */
  protected function proxyCreateInstance() {
    return $this->container->get($this->key);
  }
}
