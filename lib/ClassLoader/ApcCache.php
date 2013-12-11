<?php


class xautoload_ClassLoader_ApcCache extends xautoload_ClassLoader_AbstractCache implements xautoload_CacheManagerObserverInterface {

  /**
   * @throws Exception
   *   Throws an exception, if requirements are not satisfied.
   */
  protected function checkRequirements() {
    return extension_loaded('apc') && function_exists('apc_store');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    if ($file = apc_fetch($this->prefix . $class)) {
      if (is_file($file)) {
        require $file;
        return;
      }
      apc_delete($this->prefix . $class);
    }

    // Resolve cache miss.
    $api = new xautoload_InjectedAPI_ClassFinder_LoadClassGetFile($class);
    if ($this->finder->apiFindFile($api, $class)) {
      apc_store($this->prefix . $class, $api->getFile());
    }
  }
}
