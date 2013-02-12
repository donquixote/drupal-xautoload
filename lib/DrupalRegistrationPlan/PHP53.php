<?php

class xautoload_DrupalRegistrationPlan_PHP53 extends xautoload_DrupalRegistrationPlan_PHP52 {

  function missingDir($path_fragment) {
    $module = substr($path_fragment, 0, -1);
    $module_dir = drupal_get_path('module', $module);
    return $module_dir . '/lib/';
  }

  protected function registerExtensionFilepaths($extensions) {

    $prefix_maps = array();
    $namespace_maps = array();
    foreach ($extensions as $info) {
      $extension_dir = dirname($info->filename);
      $prefix_maps[$info->type][$info->name] = $extension_dir . '/lib';
      $namespace_maps[$info->type]['Drupal\\' . $info->name] = $extension_dir . '/lib/Drupal/' . $info->name;
    }
    $this->registerPrefixMaps($prefix_maps);
    $this->registerNamespaceMaps($namespace_maps);

    // Check if simpletest is installed.
    if (!empty($extension_filepaths['simpletest'])) {
      // Also register test namespaces.
      // TODO: Should we postpone this until later in the request?
      $this->registerSimpletestNamespaces();
    }
  }

  protected function registerNamespaceMaps($namespace_maps) {
    foreach ($namespace_maps as $type => $map) {
      $missing_dir_plugin = new xautoload_MissingDirPlugin_DrupalExtensionNamespace($type, FALSE);
      $this->finder->registerNamespacesDeep($map, $missing_dir_plugin);
    }
  }

  /**
   * Register "$module_dir/lib/Drupal/$module/Tests" namespace directories for
   * enabled and disabled modules and themes.
   */
  protected function registerSimpletestNamespaces() {
    $filepaths = db_query("SELECT name, filename from {system}")->fetchAllKeyed();
    $map = array();
    foreach ($filepaths as $name => $filepath) {
      $map['Drupal\\' . $name . '\\Tests'] = dirname($filepath) . '/lib/Drupal/' . $name . '/Tests';
    }
    $this->finder->registerNamespacesDeep($map);
  }
}
