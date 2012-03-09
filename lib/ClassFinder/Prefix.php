<?php


class xautoload_ClassFinder_Prefix {

  protected $prefixMap;

  function __construct() {
    $this->prefixMap = new xautoload_ClassFinder_Helper_RecursiveMapEvaluator();
  }

  function registerPrefixRoot($prefix, $root_path, $lazy_check = TRUE) {
    $subdir = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
    $deep_path
      = strlen($root_path)
      ? ($root_path . DIRECTORY_SEPARATOR . $subdir)
      : $subdir
    ;
    $this->registerPrefixDeepLocation($prefix, $deep_path, $lazy_check);
  }

  function registerPrefixDeep($prefix, $deep_path, $lazy_check = TRUE) {
    $this->registerPrefixDeepLocation($prefix, $deep_path, $lazy_check);
  }

  /**
   * Register a filesystem location for a given class prefix.
   *
   * @param string $prefix
   *   The prefix, without trailing underscore.
   * @param string $path
   *   The filesystem location.
   * @param boolean $lazy_check
   *   If TRUE, then we are not sure if the directory at $path actually exists.
   *   If during the process we find the directory to be nonexistent, we
   *   unregister the path.
   */
  function registerPrefixDeepLocation($prefix, $path, $lazy_check = FALSE) {
    $path_prefix_symbolic =
      strlen($prefix)
      ? str_replace('_', DIRECTORY_SEPARATOR, $prefix . '_')
      : ''
    ;
    $this->prefixMap->registerDeepPath($path_prefix_symbolic, $path . '/', $lazy_check);
  }

  function registerPrefixHandler($prefix, $handler) {
    $path_prefix_symbolic =
      strlen($prefix)
      ? str_replace('_', DIRECTORY_SEPARATOR, $prefix . '_')
      : ''
    ;
    $this->prefixMap->registerNamespaceHandler($path_prefix_symbolic, $handler);
  }

  function findFile($api, $class) {
    if ($class{0} === '_') return;
    if (false !== $pos = strrpos($class, '_')) {
      $path_prefix_symbolic = str_replace('_', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      $suffix = substr($class, $pos + 1);
    }
    else {
      $path_prefix_symbolic = '';
      $suffix = $class;
    }
    return $this->prefixMap->findFile_rec($api, $path_prefix_symbolic, $suffix . '.php', '_');
  }
}
