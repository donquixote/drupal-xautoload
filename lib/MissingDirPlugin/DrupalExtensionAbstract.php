<?php

abstract class xautoload_MissingDirPlugin_DrupalExtensionAbstract implements xautoload_MissingDirPlugin_Interface {

  /**
   * @var string string
   */
  protected $type;

  /**
   * @var bool
   */
  protected $shallow;

  /**
   * @param string $type
   *   The extension type, e.g. "module" or "theme".
   * @param bool $shallow
   *   Whether to use a "shallow" variation of PSR0 or PEAR.
   */
  function __construct($type, $shallow = FALSE) {
    $this->type = $type;
    $this->shallow = $shallow;
  }
}
