<?php


class xautoload_ClassLoader_XCache extends xautoload_ClassLoader_AbstractCache implements xautoload_CacheManagerObserverInterface {

  /**
   * @throws Exception
   *   Throws an exception, if requirements are not satisfied.
   */
  protected function checkRequirements() {
    return extension_loaded('Xcache') && function_exists('xcache_isset');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
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
    $api = new xautoload_InjectedAPI_ClassFinder_LoadClassGetFile($class);
    if ($this->finder->apiFindFile($api, $class)) {
      xcache_set($this->prefix . $class, $api->getFile());
    }
  }
}
