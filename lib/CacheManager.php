<?php

class xautoload_CacheManager {

  /**
   * @var string
   */
  protected $prefix;

  /**
   * @var xautoload_CacheManagerObserverInterface[]
   */
  protected $observers = array();

  /**
   * @param $prefix
   */
  protected function __construct($prefix) {
    $this->prefix = $prefix;
  }

  /**
   * This method has side effects, so it is not the constructor.
   *
   * @return self
   */
  static function create() {
    $prefix = variable_get('xautoload_cache_prefix', NULL);
    $manager = new self($prefix);
    if (empty($prefix)) {
      $manager->renewCachePrefix();
    }
    return $manager;
  }

  /**
   * @param xautoload_CacheManagerObserverInterface $observer
   */
  function observeCachePrefix($observer) {
    $observer->setCachePrefix($this->prefix);
    $this->observers[] = $observer;
  }

  /**
   * Renew the cache prefix, save it, and notify all observers.
   */
  function renewCachePrefix() {
    $this->prefix = xautoload_Util::randomString();
    variable_set('xautoload_cache_prefix', $this->prefix);
    foreach ($this->observers as $observer) {
      $observer->setCachePrefix($this->prefix);
    }
  }
}