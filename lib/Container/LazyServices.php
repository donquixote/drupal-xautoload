<?php

/**
 * @property xautoload_BootSchedule_Helper_PHP52|xautoload_BootSchedule_Helper_PHP53 $registrationHelper
 * @property xautoload_BootSchedule_Proxy $schedule
 * @property xautoload_LoaderManager $loaderManager
 * @property xautoload_ApcKeyManager_Disabled|xautoload_ApcKeyManager_Enabled $apcKeyManager
 * @property xautoload_ClassFinder_Proxy $proxyFinder
 * @property xautoload_ClassFinder_Interface $classFinder
 * @property xautoload_ClassFinder_Interface|xautoload_ClassFinder_Prefix|xautoload_ClassFinder_NamespaceOrPrefix| $finder
 *   Alias for ->classFinder
 */
class xautoload_Container_LazyServices {

  /**
   * @var xautoload_ServiceFactory
   */
  protected $factory;

  /**
   * @var object[]
   */
  protected $services = array();

  /**
   * @param string $key
   *
   * @return mixed
   */
  function get($key) {
    if (!isset($this->services[$key])) {
      $this->services[$key] = $this->factory->$key($this);
      if (!isset($this->services[$key])) {
        $this->services[$key] = FALSE;
      }
    }
    return $this->services[$key];
  }

  /**
   * Unset the service for a specific key.
   *
   * @param string $key
   */
  function reset($key) {
    $this->services[$key] = NULL;
  }

  /**
   * Register a new service under the given key.
   *
   * @param string $key
   * @param mixed $service
   */
  function set($key, $service) {
    $this->services[$key] = $service;
  }

  /**
   * Magic getter for a service.
   *
   * @param string $key
   *
   * @return mixed
   */
  function __get($key) {
    return $this->get($key);
  }

  /**
   * @param xautoload_ServiceFactory $factory
   */
  function __construct($factory) {
    $this->factory = $factory;
  }
}
