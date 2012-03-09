<?php


class xautoload_DrupalExtensionSystem {

  function module_exists($module) {
    return module_exists($module);
  }

  function drupal_get_path($type, $name) {
    return drupal_get_path($type, $name);
  }
}
