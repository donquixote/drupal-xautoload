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

  /**
   * @var array
   */
  protected $paths = array();

  /**
   * @var array
   */
  protected $plugins = array();

  /**
   * @var array
   *   Index of the last inserted plugin.
   *   We can't use count(), because plugins at some index can be unset.
   */
  protected $lastPluginIds = array();

  /**
   * If a class file would be in
   *   $psr0_root . '/' . $path_fragment . $path_suffix
   * then instead, we look in
   *   $root_path . '/' . $path_fragment . $path_suffix
   *
   * @param string $logical_base_path
   *   The would-be namespace path relative to PSR-0 root.
   *   That is, the namespace with '\\' replaced by DIRECTORY_SEPARATOR.
   * @param string $root_path
   *   The filesystem location of the (PSR-0) root folder for the given
   *   namespace.
   * @param boolean $lazy_check
   *   If TRUE, then it is yet unknown whether the directory exists. If during
   *   the process we find that it does not exist, we unregister it.
   */
  function registerRootPath($logical_base_path, $root_path, $lazy_check = TRUE) {
    $deep_path = $root_path . DIRECTORY_SEPARATOR . $logical_base_path;
    $this->registerDeepPath($logical_base_path, $deep_path, $lazy_check);
  }

  /**
   * If a class file would be in
   *   $psr0_root . '/' . $path_fragment . $path_suffix
   * then instead, we look in
   *   $deep_path . $path_suffix
   *
   * @param string $logical_base_path
   *   The would-be namespace path relative to PSR-0 root.
   *   That is, the namespace with '\\' replaced by DIRECTORY_SEPARATOR.
   * @param string $deep_path
   *   The filesystem location of the (PSR-0) subfolder for the given namespace.
   * @param bool $lazy_check
   *   If TRUE, then it is yet unknown whether the directory exists. If during
   *   the process we find that it does not exist, we unregister it.
   */
  function registerDeepPath($logical_base_path, $deep_path, $lazy_check = TRUE) {
    $this->paths[$logical_base_path][$deep_path] = $lazy_check;
  }

  /**
   * Register a bunch of those paths ..
   */
  function registerDeepPaths($map) {
    foreach ($map as $key => $paths) {
      if (isset($this->paths[$key])) {
        $paths += $this->paths[$key];
      }
      $this->paths[$key] = $paths;
    }
  }

  /**
   * Register a plugin for a namespace or prefix.
   *
   * @param string $logical_base_path
   *   First part of the path generated from the class name.
   * @param xautoload_FinderPlugin_Interface $plugin
   *   The plugin.
   * @param string $base_dir
   *   Id under which the plugin should be registered.
   *   This may be a numeric id, or a string key.
   *
   * @return int
   *
   * @throws Exception
   */
  function registerPlugin($logical_base_path, $plugin, $base_dir = NULL) {

    if (!isset($plugin)) {
      throw new Exception("Second argument cannot be NULL.");
    }
    elseif (!is_a($plugin, 'xautoload_FinderPlugin_Interface')) {
      throw new Exception("Second argument must implement xautoload_FinderPlugin_Interface.");
    }

    if (is_string($base_dir) && !is_numeric($base_dir)) {
      $id = $base_dir;
    }
    elseif (!isset($this->plugins[$logical_base_path])) {
      $id = ($this->lastPluginIds[$logical_base_path] = 1);
    }
    else {
      $id = ++$this->lastPluginIds[$logical_base_path];
    }
    $this->plugins[$logical_base_path][$id] = $plugin;

    if (method_exists($plugin, 'setKillswitch')) {
      // Give the plugin a red button to unregister or replace itself.
      $plugin->setKillswitch($plugin, $logical_base_path, $id);
    }

    return $id;
  }

  /**
   * Find the file for a class that in PSR-0 or PEAR would be in
   * $psr_0_root . '/' . $path_fragment . $path_suffix
   *
   * @param xautoload_InjectedAPI_findFile $api
   * @param string $logical_base_path
   *   Longest possible logical base path for the given class.
   *   Includes a trailing directory separator.
   * @param string $relative_path
   *   Remaining part of the logical path, following the $logical_base_path.
   *   Ending with '.php'.
   *
   * @return bool|NULL
   *   TRUE, if the class was found.
   */
  function findFile_map($api, $logical_base_path, $relative_path) {
    $path = $logical_base_path . $relative_path;
    while (TRUE) {
      if (isset($this->paths[$logical_base_path])) {
        $lazy_remove = FALSE;
        foreach ($this->paths[$logical_base_path] as $dir => &$lazy_check) {
          $file = $dir . $relative_path;
          if ($api->suggestFile($file)) {
            // Next time we can skip the check, because now we know that the
            // directory exists.
            $lazy_check = FALSE;
            return TRUE;
          }
          // Now we know the file does not exist. Does the directory?
          if ($lazy_check) {
            // Lazy-check whether the registered directory exists.
            if ($api->is_dir($dir)) {
              // Next time we can skip the check, because now we know that the
              // directory exists.
              $lazy_check = FALSE;
            }
            else {
              // The registered directory does not exist, so we can unregister it.
              unset($this->paths[$logical_base_path][$dir]);
              $lazy_remove = TRUE;
              if (is_object($lazy_check)) {
                /**
                 * @var xautoload_MissingDirPlugin_Interface $lazy_check
                 */
                $new_dir = $lazy_check->alternativeDir($logical_base_path);
                if ($new_dir !== $dir) {
                  $file = $new_dir . $relative_path;
                  if ($api->suggestFile($file)) {
                    $this->paths[$logical_base_path][$new_dir] = FALSE;
                    return TRUE;
                  }
                  elseif ($api->is_dir($new_dir)) {
                    $this->paths[$logical_base_path][$new_dir] = FALSE;
                  }
                }
              }
            }
          }
        }
        if ($lazy_remove && empty($this->paths[$logical_base_path])) {
          unset($this->paths[$logical_base_path]);
        }
      }

      // Check any plugin registered for this fragment.
      if (isset($this->plugins[$logical_base_path])) {
        /**
         * @var xautoload_FinderPlugin_Interface $plugin
         */
        foreach ($this->plugins[$logical_base_path] as $id => $plugin) {
          if ($plugin->findFile($api, $logical_base_path, $relative_path, $id)) {
            return TRUE;
          }
        }
      }

      // Continue with parent fragment.
      if ('' === $logical_base_path) {
        break;
      }
      elseif (DIRECTORY_SEPARATOR === $logical_base_path) {
        // This happens if a class begins with an underscore.
        $logical_base_path = '';
        $relative_path = $path;
      }
      elseif (FALSE !== $pos = strrpos($logical_base_path, DIRECTORY_SEPARATOR, -2)) {
        $logical_base_path = substr($logical_base_path, 0, $pos + 1);
        $relative_path = substr($path, $pos + 1);
      }
      else {
        $logical_base_path = '';
        $relative_path = $path;
      }
    }
  }
}
