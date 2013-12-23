<?php

namespace Drupal\xautoload\DrupalSystem;

interface DrupalSystemInterface {

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
   * Replacement of drupal_get_filename().
   *
   * @param string $type
   * @param string $name
   *
   * @return string
   */
  function drupalGetFilename($type, $name);

  /**
   * @param string[] $extension_names
   *   Extension names.
   *
   * @return string[]
   *   Extension types by extension name.
   */
  function getExtensionTypes($extension_names);

  /**
   * @return string[]
   *   Extension types by extension name.
   */
  function getActiveExtensions();
}
