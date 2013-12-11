<?php

/**
 * A placeholder class finder. Used to postpone expensive operations until they
 * are actually needed.
 */
class xautoload_ClassFinder_Proxy
  extends xautoload_ClassLoader_Abstract
  implements xautoload_ClassFinder_Interface {

  /**
   * @var xautoload_ClassFinder_ExtendedInterface
   *   The actual class finder.
   */
  protected $finder;

  /**
   * @var xautoload_Adapter_DrupalExtensionAdapter
   */
  protected $helper;

  /**
   * @var xautoload_FinderOperation_Interface[]
   *   Operations to run when the actual finder is initialized.
   */
  protected $scheduledOperations = array();

  /**
   * @var bool
   */
  protected $initialized = FALSE;

  /**
   * @param xautoload_ClassFinder_ExtendedInterface $finder
   * @param xautoload_Adapter_DrupalExtensionAdapter $helper
   */
  function __construct($finder, $helper) {
    $this->finder = $finder;
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {
    $this->initFinder();
    $this->finder->loadClass($class);
  }

  /**
   * {@inheritdoc}
   */
  function apiFindFile($api, $class) {
    $this->initFinder();
    return $this->finder->apiFindFile($api, $class);
  }

  /**
   * @param xautoload_FinderOperation_Interface $operation
   */
  function onFinderInit($operation) {
    if (!$this->initialized) {
      $this->scheduledOperations[] = $operation;
    }
    else {
      $operation->operateOnFinder($this->finder, $this->helper);
    }
  }

  /**
   * @return xautoload_ClassFinder_Interface
   */
  function getFinder() {
    $this->initFinder();
    return $this->finder;
  }

  /**
   * Initialize the finder and run scheduled operations.
   */
  protected function initFinder() {
    if (!$this->initialized) {
      foreach ($this->scheduledOperations as $operation) {
        $operation->operateOnFinder($this->finder, $this->helper);
      }
      $this->initialized = TRUE;
    }
  }
}
