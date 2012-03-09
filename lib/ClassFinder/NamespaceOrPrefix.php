<?php


class xautoload_ClassFinder_NamespaceOrPrefix extends xautoload_ClassFinder_Prefix {

  protected $namespaceMap;

  function __construct() {
    parent::__construct();
    $this->namespaceMap = new xautoload_ClassFinder_Helper_RecursiveMapEvaluator();
  }

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

  function registerNamespaceDeep($namespace, $path, $lazy_check = TRUE) {
    $this->registerNamespaceDeepLocation($namespace, $path, $lazy_check);
  }

  /**
   * Register a filesystem location for a given namespace.
   */
  function registerNamespaceDeepLocation($namespace, $path, $lazy_check = TRUE) {
    $path_prefix_symbolic = str_replace('\\', DIRECTORY_SEPARATOR, $namespace . '\\');
    $this->namespaceMap->registerDeepPath($path_prefix_symbolic, $path . '/', $lazy_check);
  }

  function registerNamespaceHandler($namespace, $handler) {
    $path_prefix_symbolic =
      strlen($namespace)
      ? str_replace('\\', DIRECTORY_SEPARATOR, $namespace . '\\')
      : ''
    ;
    $this->namespaceMap->registerNamespaceHandler($path_prefix_symbolic, $handler);
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param string $class
   *   The name of the class
   * @return string|null
   *   The path, if found
   */
  function findFile($api, $class) {

    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (false !== $pos = strrpos($class, '\\')) {
      // namespaced class name
      if ($class{$pos + 1} === '_') return;
      $path_prefix_symbolic = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      $path_suffix = str_replace('_', DIRECTORY_SEPARATOR, substr($class, $pos + 1)) . '.php';
      return $this->namespaceMap->findFile_rec($api, $path_prefix_symbolic, $path_suffix);
    }
    else {
      // class name with prefix
      return parent::findFile($api, $class);
    }
  }
}
