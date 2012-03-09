<?php


/**
 * Behaves like the Symfony ClassLoader classes.
 */
class xautoload_ClassLoader {

  protected $finder;

  function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * Registers this instance as an autoloader.
   *
   * @param Boolean $prepend Whether to prepend the autoloader or not
   *
   * @api
   */
  function register($prepend = false) {
    // http://www.php.net/manual/de/function.spl-autoload-register.php#107362
    // "when specifying the third parameter (prepend), the function will fail badly in PHP 5.2"
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
      spl_autoload_register(array($this, 'loadClass'), TRUE, $prepend);
    }
    elseif ($prepend) {
      $loaders = spl_autoload_functions();
      spl_autoload_register(array($this, 'loadClass'));
      foreach ($loaders as $loader) {
        spl_autoload_unregister($loader);
        spl_autoload_register($loader);
      }
    }
    else {
      spl_autoload_register(array($this, 'loadClass'));
    }
  }

  function loadClass($class) {
    $api = new xautoload_InjectedAPI_findFile($class);
    if ($this->finder->findFile($api, $class)) {
      require $api->getFile();
    }
    elseif (preg_match('#^xautoload_#', $class)) {
      // die("Failed to load '$class'.");
    }
  }

  function findFile($class) {
    $api = new xautoload_InjectedAPI_findFile($class);
    if ($this->finder->findFile($api, $class)) {
      return $api->getFile();
    }
  }
}
