<?php


class xautoload_ClassLoader_WinCache extends xautoload_ClassLoader_AbstractCache implements xautoload_CacheManagerObserverInterface {

  /**
   * @throws Exception
   *   Throws an exception, if requirements are not satisfied.
   */
  protected function checkRequirements() {
    return extension_loaded('wincache') && function_exists('wincache_ucache_get');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    if ($file = wincache_ucache_get($this->prefix . $class)) {
      if (is_file($file)) {
        require $file;
        return;
      }
      wincache_ucache_delete($this->prefix . $class);
    }

    // Resolve cache miss.
    $api = new xautoload_InjectedAPI_ClassFinder_LoadClassGetFile($class);
    if ($this->finder->apiFindFile($api, $class)) {
      wincache_ucache_set($this->prefix . $class, $api->getFile());
    }
  }
}
