<?php

/**
 * Variation of the APC-cached loader that does not check file_exists() for
 * files found in the cache.
 * This gives a tiny speed boost, but it will break if some files have
 * disappeared e.g. with a module update.
 */
class xautoload_ClassLoader_ApcAggressive extends xautoload_ClassLoader_ApcCache {

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    if (($file = apc_fetch($this->prefix . $class))) {
      require $file;
      return;
    }

    // Resolve cache miss.
    $api = new xautoload_InjectedAPI_ClassFinder_LoadClassGetFile($class);
    if ($this->finder->apiFindFile($api, $class)) {
      apc_store($this->prefix . $class, $api->getFile());
    }
  }
}
