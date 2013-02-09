<?php


class xautoload_ServiceFactory {

  protected $config;

  function __construct($config = array()) {
    $this->config = $config;
  }

  function classLoader($registry) {
    $finder = $registry->classFinder;
    $apc_prefix = $this->_apcPrefix();
    if (!empty($apc_prefix) && FALSE) {
      return new xautoload_ClassLoader_ApcCache($finder, $apc_prefix);
    }
    else {
      return new xautoload_ClassLoader($finder);
    }
  }

  function cachedClassFinder($registry) {
    $apc_prefix = $this->_apcPrefix();
    if (!empty($apc_prefix)) {
      $finder = $registry->classFinder;
      return new xautoload_ClassFinder_ApcCache($finder, $apc_prefix);
    }
    else {
      return $registry->classFinder;
    }
  }

  protected function _apcPrefix() {
    if (
      extension_loaded('apc') &&
      function_exists('apc_store')
    ) {
      return 'drupal.xautoload.' . $GLOBALS['drupal_hash_salt'];
    }
  }

  function classFinder($registry) {

    if (version_compare(PHP_VERSION, '5.3') >= 0) {
      // Create the finder with namespace support.
      return new xautoload_ClassFinder_NamespaceOrPrefix();
    }
    else {
      // If we are not at PHP 5.3 +, we can't have namespaces support.
      return new xautoload_ClassFinder_Prefix();
    }
  }

  function drupalPrefixPlugin($registry) {
    $system = $registry->drupalExtensionSystem;
    return new xautoload_Plugin_DrupalExtensionLibPrefixDeep($system);
  }

  function drupalNamespacePlugin($registry) {
    $system = $registry->drupalExtensionSystem;
    return new xautoload_Plugin_DrupalExtensionLibPSR0($system);
  }

  function drupalExtensionSystem() {
    return new xautoload_DrupalExtensionSystem();
  }

  function plan($registry) {
    if (version_compare(PHP_VERSION, '5.3') >= 0) {
      return new xautoload_DrupalRegistrationPlan_PHP53($registry->classFinder);
    }
    else {
      return new xautoload_DrupalRegistrationPlan_PHP52($registry->classFinder);
    }
  }
}
