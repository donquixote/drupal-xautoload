<?php

namespace Drupal\xautoload\ClassLoader;

use Drupal\xautoload\CacheManager\CacheManagerObserverInterface;
use Drupal\xautoload\ClassFinder\InjectedApi\LoadClassGetFileInjectedApi;

class ApcuClassLoader extends AbstractCachedClassLoader implements CacheManagerObserverInterface {

  /**
   * @return bool
   */
  protected function checkRequirements() {
    return extension_loaded('apcu') && function_exists('apcu_store');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    // @todo Use a suffix instead of a prefix? For faster lookup?
    // See http://stackoverflow.com/questions/39701930/is-apcu-fetch-lookup-faster-with-prefix-or-suffix
    if ($file = \apcu_fetch($this->prefix . $class)) {
      if (is_file($file)) {
        require $file;

        return;
      }
      \apcu_delete($this->prefix . $class);
    }

    // Resolve cache miss.
    $api = new LoadClassGetFileInjectedApi($class);
    if ($this->finder->apiFindFile($api, $class)) {
      \apcu_store($this->prefix . $class, $api->getFile());
    }
  }
}
