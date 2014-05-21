<?php

namespace Drupal\xautoload\CacheMissObserver;

use Drupal\xautoload\ClassLoader\AbstractClassLoaderDecorator;

class CacheMissLoaderSetFinder implements CacheMissObserverInterface {

  /**
   * @var AbstractClassLoaderDecorator
   */
  protected $loader;

  /**
   * @param AbstractClassLoaderDecorator $loader
   */
  function __construct($loader) {
    $this->loader = $loader;
  }

  /**
   * {@inheritdoc}
   */
  function operateOnFinder($finder) {
    $this->loader->setFinder($finder);
  }
}
