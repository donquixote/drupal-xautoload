<?php


class xautoload_ServiceFactory {

  protected $config;

  function __construct($config = array()) {
    $this->config = $config;
  }

  function classLoader($registry) {
    $finder = $registry->cachedClassFinder;
    return new xautoload_ClassLoader($finder);
  }

  function cachedClassFinder($registry) {
    $apc_prefix = $this->_apcPrefix();
    if (!empty($apc_prefix) && FALSE) {
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
      function_exists('apc_store') &&
      !empty($GLOBALS['databases'])
    ) {
      return 'drupal_xautoload_' . hash('sha256', serialize($GLOBALS['databases']));
    }
  }

  function classFinder($registry) {

    if (version_compare(PHP_VERSION, '5.3') >= 0) {
      // Create the finder with namespace support.
      $finder = new xautoload_ClassFinder_NamespaceOrPrefix();

      // D8-style autoloading.
      $drupal_psr0 = $registry->drupalNamespaceHandler;
      $finder->registerNamespaceHandler('Drupal', $drupal_psr0);
    }
    else {
      // If we are not at PHP 5.3 +, we can't have namespaces support.
      $finder = new xautoload_ClassFinder_Prefix();
    }

    // Register the xautoload-style PHP 5.2 compatibility solution.
    $drupal_prefix = $registry->drupalPrefixHandler;
    $finder->registerPrefixHandler('', $drupal_prefix);

    return $finder;
  }

  function drupalPrefixHandler($registry) {
    $system = $registry->drupalExtensionSystem;
    return new xautoload_NamespaceHandler_DrupalExtensionLibPrefixDeep($system);
  }

  function drupalNamespaceHandler($registry) {
    $system = $registry->drupalExtensionSystem;
    return new xautoload_NamespaceHandler_DrupalExtensionLibPSR0($system);
  }

  function drupalExtensionSystem() {
    return new xautoload_DrupalExtensionSystem();
  }
}
