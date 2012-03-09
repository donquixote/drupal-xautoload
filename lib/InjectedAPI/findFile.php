<?php


/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
class xautoload_InjectedAPI_findFile {

  protected $file;
  protected $className;

  function __construct($class_name) {
    $this->className = $class_name;
  }

  /**
   * This is done in the injected api object, so we can easily provide a mock
   * implementation.
   */
  function is_dir($dir) {
    return is_dir($dir);
  }

  function getClass() {
    return $this->className;
  }

  function suggestFile($file) {
    if (file_exists($file)) {
      $this->file = $file;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * When the process has finished, use this to return the result.
   */
  function getFile() {
    return $this->file;
  }
}
