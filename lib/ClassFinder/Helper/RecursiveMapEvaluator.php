<?php


/**
 * Helper class for the class finder.
 */
class xautoload_ClassFinder_Helper_RecursiveMapEvaluator {

  protected $nsPaths = array();
  protected $nsHandlers = array();

  /**
   * If a class file would be in
   *   $psr0_root . '/' . $path_prefix_symbolic . $path_suffix
   * then instead, we look in
   *   $root_path . '/' . $path_prefix_symbolic . $path_suffix
   */
  function registerRootPath($path_prefix_symbolic, $root_path) {
    $deep_path = $root_path . DIRECTORY_SEPARATOR . $path_prefix_symbolic;
    $this->registerDeepPath($path_prefix_symbolic, $deep_path);
  }

  /**
   * If a class file would be in
   *   $psr0_root . '/' . $path_prefix_symbolic . $path_suffix
   * then instead, we look in
   *   $deep_path . $path_suffix
   *
   * @param string $path_prefix_symbolic
   *   The would-be namespace path relative to PSR-0 root.
   *   That is, the namespace with '\\' replaced by DIRECTORY_SEPARATOR.
   * @param string $path
   *   The filesystem location of the (PSR-0) subfolder for the given namespace.
   * @param boolean $lazy_check
   *   If TRUE, then it is yet unknown whether the directory exists. If during
   *   the process we find that it does not exist, we unregister it.
   */
  function registerDeepPath($path_prefix_symbolic, $deep_path, $lazy_check = TRUE) {
    $this->nsPaths[$path_prefix_symbolic][$deep_path] = $lazy_check;
  }

  function registerNamespaceHandler($path_prefix_symbolic, $handler) {
    $this->nsHandlers[$path_prefix_symbolic][] = $handler;
  }

  /**
   * Find the file for a class that in PSR-0 or PEAR would be in
   * $psr_0_root . '/' . $path_prefix_symbolic . $path_suffix
   *
   * @param string $path_prefix_symbolic
   *   First part of the canonical path, with trailing DIRECTORY_SEPARATOR.
   * @param string $path_suffix
   *   Second part of the canonical path, ending with '.php'.
   */
  function findFile_rec($api, $path_prefix_symbolic, $path_suffix) {

    // Check any paths registered for this namespace.
    if (isset($this->nsPaths[$path_prefix_symbolic])) {
      $lazy_remove = FALSE;
      foreach ($this->nsPaths[$path_prefix_symbolic] as $dir => $lazy_check) {
        $file = $dir . $path_suffix;
        if ($api->suggestFile($file)) {
          return TRUE;
        }
        if ($lazy_check && !$api->is_dir($dir)) {
          // This is the best place to lazy-check whether a directory exists.
          unset($this->nsPaths[$path_prefix_symbolic][$dir]);
          $lazy_remove = TRUE;
        }
      }
      if ($lazy_remove && empty($this->nsPaths[$path_prefix_symbolic])) {
        unset($this->nsPaths[$path_prefix_symbolic]);
      }
    }

    // Check any handlers registered for this namespace.
    if (isset($this->nsHandlers[$path_prefix_symbolic])) {
      foreach ($this->nsHandlers[$path_prefix_symbolic] as $handler) {
        if ($handler->findFile($api, $path_prefix_symbolic, $path_suffix)) {
          return TRUE;
        }
      }
    }

    if (!strlen($path_prefix_symbolic)) {
      return NULL;
    }

    // Namespace not registered, or class not found.
    // Try with parent namespace.
    if (false !== $pos = strrpos($path_prefix_symbolic, DIRECTORY_SEPARATOR, -2)) {
      $parent_namespace_path = substr($path_prefix_symbolic, 0, $pos + 1);
      $parent_path_suffix = substr($path_prefix_symbolic, $pos + 1) . $path_suffix;
    }
    else {
      $parent_namespace_path = '';
      $parent_path_suffix = $path_prefix_symbolic . $path_suffix;
    }

    // Recursive call.
    return $this->findFile_rec($api, $parent_namespace_path, $parent_path_suffix);
  }
}
