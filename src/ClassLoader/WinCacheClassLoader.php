<?php

namespace Drupal\xautoload\ClassLoader;

use Drupal\xautoload\CacheManager\CacheManagerObserverInterface;
use Drupal\xautoload\ClassFinder\InjectedApi\LoadClassGetFileInjectedApi;

class WinCacheClassLoader extends AbstractCachedClassLoader implements CacheManagerObserverInterface {

  /**
   * @return bool
   */
  protected function checkRequirements() {
    return extension_loaded('wincache')
      && function_exists('wincache_ucache_get');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    // @todo Use a suffix instead of a prefix? For faster lookup?
    // See http://stackoverflow.com/questions/39701930/is-apcu-fetch-lookup-faster-with-prefix-or-suffix
    if ($file = wincache_ucache_get($this->prefix . $class)) {
      if (is_file($file)) {
        require $file;

        return;
      }
      wincache_ucache_delete($this->prefix . $class);
    }

    // Resolve cache miss.
    $api = new LoadClassGetFileInjectedApi($class);
    if ($this->finder->apiFindFile($api, $class)) {
      wincache_ucache_set($this->prefix . $class, $api->getFile());
    }
  }
}
