<?php

/**
 * This thing has an overview of available class loaders with different cache
 * mechanics. It can detect the currently applicable cache method, and it can
 * switch between cache methods.
 */
class xautoload_ClassLoaders {

  protected $finder;
  protected $mode;
  protected $loaders = array();

  /**
   * @param xautoload_ClassFinder_Interface $finder
   *   The class finder to plug into our loaders.
   */
  function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * Register the loader for the given mode, and unregister other loaders.
   * This can be used both for initial registration, and later on to change the
   * cache mode.
   *
   * @param string $mode
   *   Loader mode, e.g. 'apc' or 'default'.
   */
  function register($mode = NULL) {
    if (!isset($mode)) {
      $mode = $this->detectLoaderMode();
    }
    $success = $this->initLoaderMode($mode);
    if (!$success) {
      // Fallback to 'default' mode.
      $this->initLoaderMode('default');
      $mode = 'default';
    }
    $this->switchLoaderMode($mode);
  }

  /**
   * Detect the loader mode.
   *
   * @return string
   *   Loader mode, e.g. 'apc' or 'default'.
   */
  protected function detectLoaderMode() {
    if (function_exists('variable_get')) {
      return variable_get('autoload_mode', 'default');
    }
    return 'default';
  }

  /**
   * Change the loader mode.
   *
   * @param string $mode
   *   Loader mode, e.g. 'apc' or 'default'.
   */
  protected function switchLoaderMode($mode) {
    if ($mode === $this->mode) {
      return;
    }
    if (isset($this->loaders[$this->mode])) {
      // Unregister the original loader.
      $this->loaders[$this->mode]->unregister();
    }
    $this->registerLoader($this->loaders[$mode]);
    $this->mode = $mode;
  }

  /**
   * Create the loader for a given mode, if it does not exist yet.
   *
   * @param string $mode
   *   Loader mode, e.g. 'apc' or 'default'.
   *
   * @return boolean
   *   TRUE, if the loader for the mode does now exist.
   */
  protected function initLoaderMode($mode) {
    if (!isset($this->loaders[$mode])) {
      $this->loaders[$mode] = $this->buildLoader($mode);
    }
    return !empty($this->loaders[$mode]);
  }

  /**
   * Register the new loader in the correct position in the spl autoload stack.
   *
   * @param object $loader
   *   The loader to register.
   */
  protected function registerLoader($loader) {
    // TODO: Figure out correct position in spl autoload stack.
    $loader->register();
  }

  /**
   * Build a loader for a given mode.
   *
   * @param string $mode
   *   Loader mode, e.g. 'apc' or 'default'.
   *
   * @return xautoload_ClassLoader_Interface
   *   The class loader.
   */
  protected function buildLoader($mode) {

    switch ($mode) {

      case 'apc':
        if ($apc_prefix = $this->_apcPrefix()) {
          return new xautoload_ClassLoader_ApcCache($this->finder);
        }
        break;

      case 'default':
      default:
        return new xautoload_ClassLoader_NoCache($this->finder);
    }

    // Loader could not be created, because the respective cache mechanic is not available.
    return FALSE;
  }

  /**
   * Check if APC is enabled and generate a prefix.
   *
   * @return string
   *   APC cache prefix.
   */
  protected function _apcPrefix() {
    if (
      extension_loaded('apc') &&
      function_exists('apc_store')
    ) {
      return 'drupal.xautoload.' . $GLOBALS['drupal_hash_salt'];
    }
  }
}
