<?php
/**
 * This file is autoloaded with the regular uncached xautoload.
 */


/**
 * Scan directories for wildcard files[] instructions in a module's info file.
 *
 * @todo This class is a mystery! This could be done easier for sure.
 */
class xautoload_RegistryWildcard_RecursiveScan {

  /**
   * @var array
   *   Info array for the wildcard string currently being processed.
   *   This value changes for each new wildcard being processed.
   */
  protected $value;

  /**
   * @var array
   *   New entries added because they were found in a file scan. Only kept for
   *   debugging.
   */
  protected $plus = array();

  /**
   * @var array
   *   Wildcard entries removed during the process. Only kept for debugging.
   */
  protected $minus = array();

  /**
   * @var array
   *   $files array passed to hook_registry_files_alter().
   */
  protected $filesInRegistry;

  /**
   * @param array &$files_in_registry
   *   $files array passed to hook_registry_files_alter().
   */
  function __construct(&$files_in_registry) {
    $this->filesInRegistry =& $files_in_registry;
  }

  /**
   * Output the current state via devel dpm().
   */
  function dpm() {
    dpm($this->minus);
    dpm($this->plus);
  }

  /**
   * Process one registry entry.
   *
   * @param string $path
   *   File path or wildcard string.
   * @param array $value
   *   Info array registered for the path or wildcard string.
   *   E.g. array('module' => 'field', 'weight' => 0).
   *   All new entries for a wildcard will receive the same info array.
   */
  function check($path, $value) {
    $this->value = $value;
    if ($this->_check($path)) {
      unset($this->filesInRegistry[$path]);
      $this->minus[$path] = TRUE;
    }
  }

  /**
   * @param string $a
   *   Base directory, not containint any wildcard.
   * @param string $b
   *   First part containing wildcards.
   * @param null $c
   *   Second (optional) part containing wildcards.
   */
  protected function _abc($a, $b, $c = NULL) {
    if (is_dir($a)) {
      foreach (scandir($a) as $candidate) {
        if ($this->_validCand($candidate, $b)) {
          if (!isset($c)) {
            if ($b === '**') {
              $this->_abc("$a/$candidate", '**');
            }
            $this->_file("$a/$candidate");
          }
          else{
            if (!$this->_check("$a/$candidate/$c")) {
              $this->_file("$a/$candidate/$c");
            }
            if ($b === '**') {
              $this->_abc("$a/$candidate", '**', $c);
            }
          }
        }
      }
    }
  }

  /**
   * @param $candidate
   * @param $b
   *
   * @return bool|int
   */
  protected function _validCand($candidate, $b) {

    if ($candidate == '.' || $candidate == '..') {
      return FALSE;
    }
    if (strpos($candidate, '*') !== FALSE) {
      return FALSE;
    }
    if ($b == '*' || $b == '**') {
      return TRUE;
    }

    // More complex wildcard string.
    $fragments = array();
    foreach (explode('*', $b) as $fragment) {
      $fragments[] = preg_quote($fragment);
    }
    $regex = implode('.*', $fragments);
    return preg_match("/^$regex$/", $candidate);
  }

  /**
   * @param string $path
   *   File path or wildcard string.
   *
   * @return bool
   *   TRUE, if $path is a wildcard string.
   *   FALSE, if $path is a regular file path.
   */
  protected function _check($path) {
    if (preg_match('#^([^\*]*)/(.*\*.*)$#', $path, $m)) {
      /**
       * The $path has been split into $a + "/" + $b.
       *
       * @var string $a
       *   Base folder, e.g. "sites/all/modules/foo/includes", which does NOT
       *   contain any asterisk ("*").
       * @var string $b
       *   Suffix which may contain one or more asterisks, but MUST contain at
       *   least one character without an asterisk.
       */
      list(, $a, $b) = $m;
      list($b, $c) = $result = explode('/', $b, 2) + array(NULL, NULL);
      if ($b === '**' && isset($c)) {
        /**
         * $b has been further split into "**" + "/" + $c.
         */
        $this->_check("$a/$c");
      }
      $this->_abc($a, $b, $c);
      return TRUE;
    }
    else {
      // Not a wildcard string
      return FALSE;
    }
  }

  /**
   * @param string $path
   *   Add a new file path to $this->filesInRegistry().
   */
  protected function _file($path) {
    if (is_file($path)) {
      $this->filesInRegistry[$path] = $this->value;
      $this->plus[$path] = TRUE;
    }
  }
}
