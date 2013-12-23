<?php

namespace Drupal\xautoload;

use Drupal\xautoload\DIC\ServiceContainer;
use Drupal\xautoload\DIC\ServiceContainerInterface;

class Main implements ServiceContainerInterface {

  /**
   * @var ServiceContainer
   *   The service container, similar to a DIC.
   */
  protected $services;

  /**
   * @param ServiceContainer $services
   */
  function __construct($services) {
    $this->services = $services;
  }

  /**
   * @return ServiceContainer
   */
  function getServiceContainer() {
    return $this->services;
  }

  /**
   * Invalidate all values stored in APC.
   */
  function flushCache() {
    $this->services->cacheManager->renewCachePrefix();
  }

  /**
   * Register a module in early bootstrap, or from modulename.install
   *
   * @param string $file
   *   File path to a *.module or *.install file.
   */
  function registerModule($file) {
    $info = pathinfo($file);
    $name = $info['filename'];
    $dir = $info['dirname'];
    $this->extensionRegistrationService->registerExtension(
      $name,
      'module',
      $dir
    );
  }

  /**
   * Register a module as PSR-4, in early bootstrap or from modulename.install
   * This can be used while Drupal 8 is still undecided whether PSR-4 class
   * files should live in "lib" or in "src" by default.
   *
   * @param string $file
   *   File path to a *.module or *.install file.
   * @param string $subdir
   *   The PSR-4 base directory for the module namespace, relative to the module
   *   directory. E.g. "src" or "lib".
   */
  function registerModulePsr4($file, $subdir) {
    $info = pathinfo($file);
    $name = $info['filename'];
    $this->services->extensionRegistrationService->registerExtensionPsr4(
      $name,
      $info['dirname'],
      $subdir
    );
  }

  /**
   * Magic getter for service objects. This lets this class act as a proxy for
   * the service container.
   *
   * @param string $key
   * @return mixed
   */
  function __get($key) {
    return $this->services->__get($key);
  }
}
