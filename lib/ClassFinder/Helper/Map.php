<?php


/**
 * Helper class for the class finder.
 * This is not part of ClassFinder, because we want to use the same logic for
 * namespaces (PSR-0) and prefixes (PEAR).
 *
 * This thing does not actually deal with class names, but with transformed
 * paths.
 *
 * Example A:
 * When looking for a class \Aaa\Bbb\Ccc_Ddd, the class finder will
 * 1. Determine that this class is within a namespace.
 * 2. Transform that into "Aaa/Bbb/Ccc/Ddd.php".
 * 3. Check if the namespace map evaluator has anything registered for
 *   3.1. "Aaa/Bbb/"
 *   3.2. "Aaa/"
 *   3.3. ""
 *
 * Example A:
 * When looking for a class Aaa_Bbb_Ccc, the class finder will
 * 1. Determine that this class is NOT within a namespace.
 * 2. Check if a file is explicitly registered for the class itself.
 * 3. Transform the class name into "Aaa/Bbb/Ccc.php".
 * 4. Check if the prefix map evaluator has anything registered for
 *   4.1. "Aaa/Bbb/"
 *   4.2. "Aaa/"
 *   4.3. ""
 */
class xautoload_ClassFinder_Helper_Map {

  protected $nsPaths = array();
  protected $nsPlugins = array();

  // Index of the last inserted plugin.
  // We can't use count(), because plugins at some index can be unset.
  protected $lastPluginIds = array();

  /**
   * If a class file would be in
   *   $psr0_root . '/' . $first_part . $second_part
   * then instead, we look in
   *   $root_path . '/' . $first_part . $second_part
   *
   * @param string $first_part
   *   The would-be namespace path relative to PSR-0 root.
   *   That is, the namespace with '\\' replaced by DIRECTORY_SEPARATOR.
   * @param string $path
   *   The filesystem location of the (PSR-0) root folder for the given
   *   namespace.
   * @param boolean $lazy_check
   *   If TRUE, then it is yet unknown whether the directory exists. If during
   *   the process we find that it does not exist, we unregister it.
   */
  function registerRootPath($first_part, $root_path) {
    $deep_path = $root_path . DIRECTORY_SEPARATOR . $first_part;
    $this->registerDeepPath($first_part, $deep_path);
  }

  /**
   * If a class file would be in
   *   $psr0_root . '/' . $first_part . $second_part
   * then instead, we look in
   *   $deep_path . $second_part
   *
   * @param string $first_part
   *   The would-be namespace path relative to PSR-0 root.
   *   That is, the namespace with '\\' replaced by DIRECTORY_SEPARATOR.
   * @param string $path
   *   The filesystem location of the (PSR-0) subfolder for the given namespace.
   * @param boolean $lazy_check
   *   If TRUE, then it is yet unknown whether the directory exists. If during
   *   the process we find that it does not exist, we unregister it.
   */
  function registerDeepPath($first_part, $deep_path, $lazy_check = TRUE) {
    $this->nsPaths[$first_part][$deep_path] = $lazy_check;
  }

  function registerDeepPaths($map) {
    $this->nsPaths = $map + $this->nsPaths;
  }

  /**
   * Register a plugin for a namespace or prefix.
   *
   * @param string $first_part
   *   First part of the path generated from the class name.
   * @param xautoload_Plugin_Interface $plugin
   *   The plugin.
   */
  function registerNamespacePlugin($first_part, $plugin) {

    if (!isset($plugin)) {
      throw new Exception("Second argument cannot be NULL.");
    }
    elseif (!is_a($plugin, 'xautoload_Plugin_Interface')) {
      throw new Exception("Second argument must implement xautoload_Plugin_Interface.");
    }

    if (!isset($this->nsPlugins[$first_part])) {
      $id = $this->lastPluginIds[$first_part] = 1;
    }
    else {
      $id = ++$this->lastPluginIds[$first_part];
    }
    $this->nsPlugins[$first_part][$id] = $plugin;

    if (method_exists($plugin, 'setKillswitch')) {
      // Give the plugin a red button to unregister or replace itself.
      $plugin->setKillswitch($plugin, $first_part, $id);
    }

    return $id;
  }

  /**
   * Find the file for a class that in PSR-0 or PEAR would be in
   * $psr_0_root . '/' . $first_part . $second_part
   *
   * @param string $first_part
   *   First part of the canonical path, with trailing DIRECTORY_SEPARATOR.
   * @param string $second_part
   *   Second part of the canonical path, ending with '.php'.
   */
  function findFile_nested($api, $first_part, $second_part) {

    // Check any paths registered for this namespace.
    if (isset($this->nsPaths[$first_part])) {
      $lazy_remove = FALSE;
      foreach ($this->nsPaths[$first_part] as $dir => $lazy_check) {
        $file = $dir . $second_part;
        if ($api->suggestFile($file)) {
          return TRUE;
        }
        if ($lazy_check && !$api->is_dir($dir)) {
          // This is the best place to lazy-check whether a directory exists.
          unset($this->nsPaths[$first_part][$dir]);
          $lazy_remove = TRUE;
        }
      }
      if ($lazy_remove && empty($this->nsPaths[$first_part])) {
        unset($this->nsPaths[$first_part]);
      }
    }

    // Check any plugin registered for this namespace.
    if (isset($this->nsPlugins[$first_part])) {
      foreach ($this->nsPlugins[$first_part] as $plugin) {
        if ($plugin->findFile($api, $first_part, $second_part)) {
          return TRUE;
        }
      }
    }
  }
}
