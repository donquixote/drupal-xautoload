<?php


abstract class xautoload_ClassLoader_AbstractCache extends xautoload_ClassLoader_AbstractDecorator implements xautoload_CacheManagerObserverInterface {

  /**
   * @var string
   */
  protected $prefix;

  /**
   * This method has side effects, so it is not the constructor.
   *
   * @param xautoload_ClassFinder_Interface $finder
   * @param xautoload_CacheManager $cacheManager
   *
   * @return self
   *
   * @throws Exception
   */
  static function create($finder, $cacheManager) {
    /** @var self $loader */
    $loader = new static($finder);
    if (!$loader->checkRequirements()) {
      throw new Exception('Unable to use ' . get_class($loader) . ', because the extension is not enabled.');
    }
    $cacheManager->observeCachePrefix($loader);
    return $loader;
  }

  /**
   * @throws Exception
   *   Throws an exception, if requirements are not satisfied.
   */
  protected abstract function checkRequirements();

  /**
   * {@inheritdoc}
   */
  function setCachePrefix($prefix) {
    $this->prefix = $prefix;
  }
}
