<?php


namespace Drupal\xautoload\Tests\FinderOperation;


class BootPhase implements \xautoload_FinderOperation_Interface {

  /**
   * @var string[]
   */
  protected $extensions;

  /**
   * @param string[] $extensions
   */
  function __construct(array $extensions) {
    $this->extensions = $extensions;
  }

  /**
   * {@inheritdoc}
   */
  function operateOnFinder($finder, $helper) {
    $helper->registerExtensions($this->extensions);
  }
}