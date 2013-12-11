<?php


/**
 * Behaves mostly like the Symfony ClassLoader classes.
 */
abstract class xautoload_ClassLoader_AbstractDecorator extends xautoload_ClassLoader_Abstract {

  /**
   * @var xautoload_ClassFinder_Interface
   */
  protected $finder;

  /**
   * @param xautoload_ClassFinder_Interface $finder
   *   The object that does the actual class finding.
   */
  protected function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * Replace the finder with another one.
   *
   * @param xautoload_ClassFinder_Interface $finder
   *   The object that does the actual class finding.
   */
  function setFinder($finder) {
    $this->finder = $finder;
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {
    $this->finder->loadClass($class);
  }
}
