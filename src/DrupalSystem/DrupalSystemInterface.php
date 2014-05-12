<?php

namespace Drupal\xautoload\DrupalSystem;

interface DrupalSystemInterface {

  /**
   * Wrapper for variable_set()
   *
   * @param string $name
   * @param mixed $value
   */
  function variableSet($name, $value);

  /**
   * Replacement of variable_get().
   *
   * @param string $name
   * @param mixed $default
   *
   * @return mixed
   */
  function variableGet($name, $default = NULL);

  /**
   * Replacement of drupal_get_filename(), but returning an absolute path.
   *
   * @param string $type
   * @param string $name
   *
   * @return string
   *   The result of drupal_get_filename() with DRUPAL_ROOT . '/' prepended.
   */
  function drupalGetFilename($type, $name);

  /**
   * @see drupal_get_path()
   *
   * @param string $type
   * @param string $name
   *
   * @return string
   */
  function drupalGetPath($type, $name);

  /**
   * @param string[] $extension_names
   *   Extension names.
   *
   * @return string[]
   *   Extension types by extension name.
   */
  function getExtensionTypes($extension_names);

  /**
   * Gets active extensions directly from the system table.
   *
   * @return string[]
   *   Extension types by extension name.
   */
  function getActiveExtensions();

  /**
   * Wrapper for module_list()
   *
   * @return array
   */
  function moduleList();

  /**
   * Wrapper for module_implements()
   *
   * @param string $hook
   *
   * @return array[]
   */
  function moduleImplements($hook);

  /**
   * @see libraries_info()
   *
   * @return mixed
   */
  function getLibrariesInfo();

  /**
   * @see libraries_get_path()
   *
   * @param string $name
   *   Name of the library.
   *
   * @return string|false
   */
  function librariesGetPath($name);
}
