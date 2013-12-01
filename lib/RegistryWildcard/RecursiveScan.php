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
    if (preg_match('#^([^\*]*)/(.*\*.*)$#', $path, $m)) {
      list(, $base, $wildcard) = $m;
      $this->scanDirectory($base, $wildcard);
      unset($this->filesInRegistry[$path]);
    }
  }

  /**
   * @param string $dir
   *   Base folder, e.g. "sites/all/modules/foo/includes", which does NOT
   *   contain any asterisk ("*").
   * @param string $wildcard
   *   Suffix which may contain asterisks.
   */
  protected function scanDirectory($dir, $wildcard) {
    if (!is_dir($dir)) {
      return;
    }
    if (FALSE === strpos($wildcard, '*')) {
      // $wildcard is a fixed string, not a wildcard.
      $this->suggestFile($dir . '/' . $wildcard);
    }
    elseif ('**' === $wildcard) {
      // Trick: "$a/**" == union of "$a/*" and "$a/*/**"
      $this->scanDirectoryLevel($dir, '*');
      $this->scanDirectoryLevel($dir, '*', '**');
    }
    elseif ('**/' === substr($wildcard, 0, 3)) {
      // Trick: "$a/**/$b" == union of "$a/$b" and "$a/*/**/$b"
      $remaining = substr($wildcard, 3);
      $this->scanDirectory($dir, $remaining);
      $this->scanDirectoryLevel($dir, '*', $wildcard);
    }
    elseif (FALSE !== ($slashpos = strpos($wildcard, '/'))) {
      // $wildcard consists of more than one fragment.
      $fragment = substr($wildcard, 0, $slashpos);
      $remaining = substr($wildcard, $slashpos + 1);
      if (FALSE === strpos($fragment, '*')) {
        $this->scanDirectory($dir . '/' . $fragment, $remaining);
      }
      else {
        $this->scanDirectoryLevel($dir, $fragment, $remaining);
      }
    }
    else {
      // $wildcard represents a file name.
      $this->scanDirectoryLevel($dir, $wildcard);
    }
  }

  /**
   * @param string $dir
   *   Base directory, not containing any wildcard.
   * @param string $fragment
   *   Wildcard path fragment to be processed now. This is never '**', but it
   *   always contains at least one asterisk.
   * @param null $remaining
   *   Optional rest of the wildcard string, that may contain path fragments to
   *   be processed later.
   *
   * @throws Exception
   */
  protected function scanDirectoryLevel($dir, $fragment, $remaining = NULL) {

    if (!is_dir($dir)) {
      return;
    }

    if ('**' === $fragment) {
      throw new Exception("Fragment must not be '**'.");
    }

    foreach (scandir($dir) as $candidate) {
      if (!$this->validateCandidate($candidate, $fragment)) {
        continue;
      }

      if (!isset($remaining)) {
        $this->suggestFile($dir . '/' . $candidate);
      }
      else {
        $this->scanDirectory($dir . '/' . $candidate, $remaining);
      }
    }
  }

  /**
   * @param $candidate
   *   String to be checked against the wildcard.
   * @param $wildcard
   *   Wildcard string like '*', '*.*' or '*.inc'.
   *
   * @return bool
   *   TRUE, if $candidate matches $wildcard.
   */
  protected function validateCandidate($candidate, $wildcard) {

    if ($candidate == '.' || $candidate == '..') {
      return FALSE;
    }
    if (strpos($candidate, '*') !== FALSE) {
      return FALSE;
    }
    if ($wildcard == '*' || $wildcard == '**') {
      return TRUE;
    }

    // More complex wildcard string.
    $fragments = array();
    foreach (explode('*', $wildcard) as $fragment) {
      $fragments[] = preg_quote($fragment);
    }
    $regex = implode('.*', $fragments);
    return preg_match("/^$regex$/", $candidate);
  }

  /**
   * @param string $path
   *   Add a new file path to $this->filesInRegistry().
   */
  protected function suggestFile($path) {
    if (is_file($path)) {
      $this->filesInRegistry[$path] = $this->value;
    }
  }
}
