<?php

class xautoload_BootSchedule_Helper_PHP53 extends xautoload_BootSchedule_Helper_Base {

  /**
   * Register prefixes and namespaces for enabled Drupal extensions (modules/themes).
   *
   * @param stdClass[] $extensions
   *   Info about extensions.
   */
  function registerExtensions($extensions) {

    $prefix_maps = array();
    $namespace_maps = array();
    $psr4_maps = array();
    foreach ($extensions as $info) {
      $extension_dir = dirname($info->filename);
      $prefix_maps[$info->type][$info->name] = $extension_dir . '/lib';
      $namespace_maps[$info->type]['Drupal\\' . $info->name] = $extension_dir . '/lib/Drupal/' . $info->name;
      $psr4_maps[$info->type]['Drupal\\' . $info->name] = $extension_dir . '/lib';
    }
    $this->registerPrefixMaps($prefix_maps);
    $this->registerNamespaceMaps($namespace_maps);
    $this->registerPsr4Maps($psr4_maps);
  }
}
