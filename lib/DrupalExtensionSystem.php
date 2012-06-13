<?php


class xautoload_DrupalExtensionSystem {

  function module_exists($module) {
    return module_exists($module);
  }

  function drupal_get_path($type, $name) {
    return drupal_get_path($type, $name);
  }

  function extensionExists($name) {
    if (module_exists($name)) {
      return TRUE;
    }
    $themes = list_themes();
    if (isset($themes[$name])) {
      return TRUE;
    }
  }

  function getExtensionPath($name) {
    foreach (array('module', 'theme') as $type) {
      $candidate = drupal_get_path($type, $name);
      if (!empty($candidate)) {
        return $candidate;
      }
    }
  }
}
