<?php

class xautoload_FinderOperation_LoaderSetFinder implements xautoload_FinderOperation_Interface {

  /**
   * @var xautoload_ClassLoader_AbstractDecorator
   */
  protected $loader;

  /**
   * @param xautoload_ClassLoader_AbstractDecorator $loader
   */
  function __construct($loader) {
    $this->loader = $loader;
  }

  /**
   * {@inheritdoc}
   */
  function operateOnFinder($finder, $helper) {
    $this->loader->setFinder($finder);
  }
}