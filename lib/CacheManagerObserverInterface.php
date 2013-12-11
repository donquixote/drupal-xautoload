<?php

interface xautoload_CacheManagerObserverInterface {

  /**
   * Set the APC prefix after a flush cache.
   *
   * @param string $prefix
   *   A prefix for the storage key in APC.
   */
  function setCachePrefix($prefix);
}