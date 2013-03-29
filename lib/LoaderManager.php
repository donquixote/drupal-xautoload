<?php

/**
 * This thing has an overview of available class loaders with different cache
 * mechanics. It can detect the currently applicable cache method, and it can
 * switch between cache methods.
 *
 * It should be mentioned that "loader" and "finder" mean two separate things
 * in xautoload. The "finder" knows all the namespaces and directories. The
 * "loader" is for the cache layer and for file inclusion, and it is plugged
 * with a "finder" to actually find the class on a cache miss.
 */
class xautoload_LoaderManager {

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
   * @param boolean $prepend
   *   If TRUE, the loader will be prepended before other loaders.
   *   If FALSE, the loader will be inserted into the dedicated position between
   *     other loaders.
   */
  function register($mode = NULL, $prepend = FALSE) {
    if (!isset($mode)) {
      $mode = $this->detectLoaderMode();
    }
    $success = $this->initLoaderMode($mode);
    if (!$success) {
      // Fallback to 'default' mode.
      $this->initLoaderMode('default');
      $mode = 'default';
    }
    $this->switchLoaderMode($mode, $prepend);
  }

  /**
   * Invalidate the APC cache
   */
  function flushCache() {

    if ($this->apcSupported()) {

      // Generate a new APC prefix
      $apc_prefix = $this->generateApcPrefix();

      // Set it in all apc-based loaders.
      foreach ($this->loaders as $loader_key => $loader) {
        if (1
          && 'apc_' === substr($loader_key . '_', 0, 4)
          && method_exists($loader, 'setApcPrefix')
        ) {
          $loader->setApcPrefix($apc_prefix);
        }
      }
    }
  }

  /**
   * Detect the loader mode.
   *
   * @return string
   *   Loader mode, e.g. 'apc' or 'default'.
   */
  protected function detectLoaderMode() {
    if (function_exists('variable_get')) {
      $mode = variable_get('autoloader_mode', 'default');
      return $mode;
    }
    return 'default';
  }

  /**
   * Change the loader mode.
   *
   * @param string $mode
   *   Loader mode, e.g. 'apc' or 'default'.
   * @param boolean $prepend
   *   If TRUE, the loader will be prepended before other loaders.
   *   If FALSE, the loader will be inserted into the dedicated position between
   *     other loaders.
   */
  protected function switchLoaderMode($mode, $prepend) {
    if ($mode === $this->mode && !$prepend) {
      return;
    }
    if (isset($this->loaders[$this->mode])) {
      // Unregister the original loader.
      $this->loaders[$this->mode]->unregister();
    }
    $this->registerLoader($this->loaders[$mode], $prepend);
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
  protected function registerLoader($loader, $prepend) {
    // TODO: Figure out correct position in spl autoload stack.
    $loader->register($prepend);
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

      case 'apc_lazy':
        if ($apc_prefix = $this->apcPrefix()) {
          $loader = new xautoload_ClassLoader_ApcCache($this->finder, $apc_prefix);
          $finder_wrapper = new xautoload_ClassFinder_LazyWrapper($loader, $this->finder, xautoload('plan'));
          $loader->setFinder($finder_wrapper);
          return $loader;
        }
        break;

      case 'apc':
        if ($apc_prefix = $this->apcPrefix()) {
          return new xautoload_ClassLoader_ApcCache($this->finder, $apc_prefix);
        }
        break;

      case 'default':
      case 'dev':
      default:
        return new xautoload_ClassLoader_NoCache($this->finder);
    }

    // Loader could not be created, because the respective cache mechanic is not available.
    return FALSE;
  }

  /**
   * Check if APC is enabled, and generate a prefix.
   *
   * @return string
   *   APC cache prefix.
   */
  protected function apcPrefix() {
    if ($this->apcSupported()) {
      $apc_prefix = apc_fetch($this->apcKey());
      if (empty($apc_prefix)) {
        $apc_prefix = $this->generateApcPrefix();
      }
      return $apc_prefix;
    }
  }

  /**
   * Generate a new prefix and save it to APC.
   */
  protected function generateApcPrefix() {

    // Generate a new APC prefix
    $apc_prefix = xautoload_Util::randomString();

    // Store the APC prefix
    apc_store($this->apcKey(), $apc_prefix);

    return $apc_prefix;
  }

  /**
   * Test if the APC extension is installed and working.
   */
  protected function apcSupported() {
    return 1
      && extension_loaded('apc')
      && function_exists('apc_store')
      && function_exists('apc_fetch')
    ;
  }

  /**
   * Get the key in APC where the APC prefix is stored.
   */
  protected function apcKey() {
    return 'drupal.xautoload.' . $GLOBALS['drupal_hash_salt'] . '.apc_prefix';
  }
}
