<?php


class xautoload_ClassFinder_NamespaceOrPrefix extends xautoload_ClassFinder_Prefix {

  protected $namespaceMap;

  function __construct() {
    parent::__construct();
    $this->namespaceMap = new xautoload_ClassFinder_Helper_Map();
  }

  /**
   * Register a PSR-0 root folder for a given namespace.
   *
   * @param string $namespace
   *   The namespace, e.g. "My\Namespace", to cover all classes within that,
   *   e.g. My\Namespace\SomeClass, or My\Namespace\Xyz\SomeClass. This does not
   *   cover the root-level class, e.g. My\Namespace
   * @param string $path
   *   The deep path, e.g. "../lib", if classes reside in e.g.
   *   My\Namespace\SomeClass -> ../lib/My/Namespace/SomeClass.php
   * @param boolean $lazy_check
   *   If TRUE, then we are not sure if the directory at $path actually exists.
   *   If during the process we find the directory to be nonexistent, we
   *   unregister the path.
   */
  function registerNamespaceRoot($namespace, $root_path, $lazy_check = TRUE) {
    $subdir = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    $deep_path
      = !strlen($root_path)
      ? $subdir
      : !strlen($subdir)
      ? $root_path
      : ($root_path . DIRECTORY_SEPARATOR . $subdir)
    ;
    $this->registerNamespaceDeepLocation($namespace, $deep_path, $lazy_check);
  }

  /**
   * Alias for registerNamespaceDeepLocation()
   *
   * @param string $namespace
   *   The namespace, e.g. "My\Namespace"
   * @param string $path
   *   The deep path, e.g. "../lib/My/Namespace"
   * @param boolean $lazy_check
   *   If TRUE, then we are not sure if the directory at $path actually exists.
   *   If during the process we find the directory to be nonexistent, we
   *   unregister the path.
   */
  function registerNamespaceDeep($namespace, $path, $lazy_check = TRUE) {
    $this->registerNamespaceDeepLocation($namespace, $path, $lazy_check);
  }

  /**
   * Register a deep filesystem location for a given namespace.
   *
   * @param string $namespace
   *   The namespace, e.g. "My\Namespace"
   * @param string $path
   *   The deep path, e.g. "../lib/My/Namespace"
   * @param boolean $lazy_check
   *   If TRUE, then we are not sure if the directory at $path actually exists.
   *   If during the process we find the directory to be nonexistent, we
   *   unregister the path.
   */
  function registerNamespaceDeepLocation($namespace, $path, $lazy_check = TRUE) {
    $path_prefix_symbolic = str_replace('\\', DIRECTORY_SEPARATOR, $namespace . '\\');
    $this->namespaceMap->registerDeepPath($path_prefix_symbolic, $path . '/', $lazy_check);
  }

  /**
   * Register a plugin for a namespace.
   *
   * @param string $namespace
   *   The namespace, e.g. "My\Library"
   * @param xautoload_Plugin_Interface $plugin
   *   The plugin.
   */
  function registerNamespacePlugin($namespace, $plugin) {
    $path_prefix_symbolic =
      strlen($namespace)
      ? str_replace('\\', DIRECTORY_SEPARATOR, $namespace . '\\')
      : ''
    ;
    $this->namespaceMap->registerNamespacePlugin($path_prefix_symbolic, $plugin);
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param xautoload_InjectedAPI_findFile $api
   *   API object with a suggestFile() method.
   *   We are supposed to call $api->suggestFile($file) with all suggestions we
   *   can find, until it returns TRUE. Once suggestFile() returns TRUE, we stop
   *   and return TRUE as well. The $file will be in the $api object, so we
   *   don't need to return it.
   * @param string $class
   *   The name of the class, with all namespaces prepended.
   *   E.g. Some\Namespace\Some\Class
   *
   * @return TRUE|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  function findFile($api, $class) {

    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (FALSE !== $pos = strrpos($class, '\\')) {

      // The class is within a namespace.
      if ($class{$pos + 1} === '_') {
        // We do not autoload classes where the class name begins with '_'.
        return;
      }

      // Loop through positions of '\\', backwards.
      $first_part = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      $second_part = str_replace('_', DIRECTORY_SEPARATOR, substr($class, $pos + 1)) . '.php';
      $path = $first_part . $second_part;
      while (TRUE) {
        if ($this->namespaceMap->findFile_nested($api, $first_part, $second_part)) {
          return TRUE;
        }
        $pos = strrpos($first_part, DIRECTORY_SEPARATOR, -2);
        if (FALSE === $pos) break;
        $first_part = substr($path, 0, $pos + 1);
        $second_part = substr($path, $pos + 1);
      }

      // Check if anything is registered for the root namespace.
      if ($this->namespaceMap->findFile_nested($api, '', $path)) {
        return TRUE;
      }
    }
    else {

      // The class is not within a namespace.
      // Fall back to the prefix-based finder.
      return parent::findFile($api, $class);
    }
  }
}
