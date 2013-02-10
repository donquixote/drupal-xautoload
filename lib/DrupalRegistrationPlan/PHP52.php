<?php

class xautoload_DrupalRegistrationPlan_PHP52 {

  protected $finder;

  function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * Register prefixes and namespaces for enabled Drupal extensions.
   * (for namespaces, look at xautoload_DrupalRegistrationPlan_PHP53)
   */
  function start() {
    // Doing this directly tends to be a hell lot faster than system_list().
    // TODO:
    //   Is it safe to do this with a simple query?
    //   Do we need to expect any reset events?
    $extension_filepaths = db_query("SELECT name, filename from {system} WHERE status = 1")->fetchAllKeyed();
    $this->registerExtensionFilepaths($extension_filepaths);
  }

  /**
   * Add modules after they have been enabled or installed.
   *
   * @param array $modules
   *   Array of module names, with numeric keys.
   */
  function addModules(array $modules) {
    $q = db_select('system');
    $q->condition('name', $modules);
    $q->fields('system', array('name', 'filename'));
    $extension_filepaths = $q->execute()->fetchAllKeyed();
    $this->registerExtensionFilepaths($extension_filepaths);
  }

  /**
   * Register prefixes for enabled Drupal extensions (modules/themes).
   *
   * @param array $extension_filepaths
   *   Associative array, keys are extension names, values are file paths.
   */
  protected function registerExtensionFilepaths($extension_filepaths) {
    $prefix_map = array();
    foreach ($extension_filepaths as $name => $filepath) {
      $prefix_map[$name] = dirname($filepath) . '/lib';
    }
    $this->finder->registerPrefixesDeep($prefix_map);
  }
}
