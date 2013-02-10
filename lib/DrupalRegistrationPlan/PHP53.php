<?php

class xautoload_DrupalRegistrationPlan_PHP53 extends xautoload_DrupalRegistrationPlan_PHP52 {

  protected function registerExtensionFilepaths($extension_filepaths) {

    $prefix_map = array();
    $namespace_map = array();
    foreach ($extension_filepaths as $name => $filepath) {
      $extension_dir = dirname($filepath);
      $prefix_map[$name] = $extension_dir . '/lib';
      $namespace_map['Drupal\\' . $name] = $extension_dir . '/lib/Drupal/' . $name;
    }
    $this->finder->registerPrefixesDeep($prefix_map);
    $this->finder->registerNamespacesDeep($namespace_map);

    // Check if simpletest is installed.
    if (!empty($extension_filepaths['simpletest'])) {
      // Also register test namespaces.
      // TODO: Should we postpone this until later in the request?
      $this->registerSimpletestNamespaces();
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
