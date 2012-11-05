<?php


class xautoload_ClassFinder_Prefix implements xautoload_ClassFinder_Interface {

  protected $prefixMap;
  protected $classes = array();

  function __construct() {
    $this->prefixMap = new xautoload_ClassFinder_Helper_RecursiveMapEvaluator();
  }

  /**
   * Register a PEAR-style root path for a given class prefix.
   *
   * @param string $prefix
   *   Prefix, e.g. "My_Prefix", for classes like "My_Prefix_SomeClass".
   *   This does ALSO cover the class named "My_Prefix" itself.
   * @param string $root_path
   *   Root path, e.g. "../lib" or "../src", so that classes can be placed e.g.
   *   My_Prefix_SomeClass -> ../lib/My/Prefix/SomeClass.php
   *   My_Prefix -> ../lib/My/Prefix.php
   */
  function registerPrefixRoot($prefix, $root_path, $lazy_check = TRUE) {
    $subdir = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
    $deep_path
      = strlen($root_path)
      ? ($root_path . DIRECTORY_SEPARATOR . $subdir)
      : $subdir
    ;
    $this->registerPrefixDeepLocation($prefix, $deep_path, $lazy_check);
    // We assume that the class named $prefix is also found at this path.
    $this->registerClass($prefix, $deep_path . '.php');
  }

  /**
   * Register a PEAR-style deep path for a given class prefix.
   *
   * Note:
   *   This actually goes beyond PEAR style, because it also allows things like
   *     my_library_Some_Class -> (library dir)/src/Some/Class.php
   *   instead of
   *     my_library_Some_Class -> (library dir)/src/my/library/Some/Class.php
   *   via
   *     $finder->registerPrefixDeep('my_library', "$library_dir/src");
   *
   * @param string $prefix
   *   Prefix, e.g. "My_Prefix", for classes like "My_Prefix_SomeClass".
   *   This does NOT cover the class named "My_Prefix" itself.
   * @param string $deep_path
   *   The deep path, e.g. "../lib/My/Prefix", for classes placed in
   *   My_Prefix_SomeClass -> ../lib/My/Prefix/SomeClass.php
   */
  function registerPrefixDeep($prefix, $deep_path, $lazy_check = TRUE) {
    $this->registerPrefixDeepLocation($prefix, $deep_path, $lazy_check);
  }

  /**
   * Register a filepath for an individual class.
   *
   * @param string $class
   *   The class, e.g. My_Class
   * @param string $file_path
   *   The path, e.g. "../lib/My/Class.php".
   */
  function registerClass($class, $file_path) {
    $this->classes[$class] = $file_path;
  }

  /**
   * Register a filesystem location for a given class prefix.
   *
   * @param string $prefix
   *   The prefix, e.g. "My_Prefix"
   * @param string $path
   *   The deep filesystem location, e.g. "../lib/My/Prefix".
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

  /**
   * Register a handler for a prefix.
   *
   * @param string $prefix
   *   The prefix, e.g. "My_Library"
   * @param xautoload_NamespaceHandler_Interface $handler
   *   The handler. See 
   */
  function registerPrefixHandler($prefix, $handler) {
    $path_prefix_symbolic =
      strlen($prefix)
      ? str_replace('_', DIRECTORY_SEPARATOR, $prefix . '_')
      : ''
    ;
    $this->prefixMap->registerNamespaceHandler($path_prefix_symbolic, $handler);
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

    // First check if the literal class name is registered.
    if (isset($this->classes[$class])) {
      if ($api->suggestFile($this->classes[$class])) {
        return TRUE;
      }
    }

    if ($class{0} === '_') {
      // We don't autoload classes that begin with '_'.
      return;
    }

    if (FALSE !== $pos = strrpos($class, '_')) {

      // Class does contain one or more '_' symbols.
      // Determine the canonical path.
      $path = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

      // Loop through all '/', backwards.
      do {
        $first_part = substr($path, 0, $pos + 1);
        $second_part = substr($path, $pos + 1);
        if ($this->prefixMap->findFile_nested($api, $first_part, $second_part)) {
          return TRUE;
        }
        $pos = strrpos($first_part, DIRECTORY_SEPARATOR, -2);
      } while (FALSE !== $pos);

      // Check if anything is registered for '' prefix.
      if ($this->prefixMap->findFile_nested($api, '', $path)) {
        return TRUE;
      }
    }
  }
}
