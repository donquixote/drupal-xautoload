<?php

class xautoload_DrupalRegistrationPlan_PHP53 extends xautoload_DrupalRegistrationPlan_PHP52 {

  /**
   * Register prefixes and namespaces for enabled Drupal extensions (modules/themes).
   *
   * @param array $extensions
   *   Info about extensions.
   */
  protected function registerExtensions($extensions) {

    $prefix_maps = array();
    $namespace_maps = array();
    foreach ($extensions as $info) {
      $extension_dir = dirname($info->filename);
      $prefix_maps[$info->type][$info->name] = $extension_dir . '/lib';
      $namespace_maps[$info->type]['Drupal\\' . $info->name] = $extension_dir . '/lib/Drupal/' . $info->name;
    }
    $this->registerPrefixMaps($prefix_maps);
    $this->registerNamespaceMaps($namespace_maps);
  }

  /**
   * Register namespace maps, one map per extension type.
   *
   * @param array $namespace_maps
   *   Namespace maps for different extension types. Modules and themes are
   *   registered speparately, because they need a different MissingDirPlugin.
   */
  protected function registerNamespaceMaps($namespace_maps) {
    foreach ($namespace_maps as $type => $map) {
      $missing_dir_plugin = new xautoload_MissingDirPlugin_DrupalExtensionNamespace($type, FALSE);
      $this->finder->registerNamespacesDeep($map, $missing_dir_plugin);
    }
  }
}
