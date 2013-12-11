<?php


/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
class xautoload_InjectedAPI_ClassFinder_LoadClass extends xautoload_InjectedAPI_ClassFinder_Abstract {

  /**
   * {@inheritdoc}
   */
  function suggestFile($file) {
    if (file_exists($file)) {
      require $file;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  function suggestFile_skipFileExists($file) {
    require $file;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function suggestFile_checkNothing($file) {
    require $file;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function suggestFile_checkIncludePath($file) {
    if (FALSE !== $file = xautoload_Util::findFileInIncludePath($file)) {
      require $file;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  function guessFile($file) {
    require_once $file;
    return xautoload_Util::classIsDefined($this->className);
  }

  /**
   * {@inheritdoc}
   */
  function guessPath($file) {
    if (file_exists($file)) {
      require_once $file;
      return xautoload_Util::classIsDefined($this->className);
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  function claimFile($file) {
    require $file;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function claimPath($file) {
    if (file_exists($file)) {
      require $file;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
