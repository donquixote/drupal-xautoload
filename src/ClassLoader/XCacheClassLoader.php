<?php

namespace Drupal\xautoload\ClassLoader;

use Drupal\xautoload\CacheManager\CacheManagerObserverInterface;
use Drupal\xautoload\ClassFinder\InjectedApi\LoadClassGetFileInjectedApi;

class XCacheClassLoader extends AbstractCachedClassLoader implements CacheManagerObserverInterface {

  /**
   * @return bool
   */
  protected function checkRequirements() {
    return extension_loaded('Xcache') && function_exists('xcache_isset');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    // @todo Use a suffix instead of a prefix? For faster lookup?
    // See http://stackoverflow.com/questions/39701930/is-apcu-fetch-lookup-faster-with-prefix-or-suffix
    if (xcache_isset($this->prefix . $class)
      && $file = xcache_get($this->prefix . $class)
    ) {
      if (is_file($file)) {
        require $file;

        return;
      }
      xcache_unset($this->prefix . $class);
    }

    // Resolve cache miss.
    $api = new LoadClassGetFileInjectedApi($class);
    if ($this->finder->apiFindFile($api, $class)) {
      xcache_set($this->prefix . $class, $api->getFile());
    }
  }
}
