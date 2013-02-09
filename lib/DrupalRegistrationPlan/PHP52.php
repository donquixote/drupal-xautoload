<?php

class xautoload_DrupalRegistrationPlan_PHP52 {

  protected $finder;

  function __construct($finder) {
    $this->finder = $finder;
  }

  function start() {
    // Doing this directly is typically a hell lot faster than system_list().
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

  protected function registerExtensionFilepaths($extension_filepaths) {
    $prefix_map = array();
    foreach ($extension_filepaths as $name => $filepath) {
      if (FALSE !== $rpos = strrpos($filepath, '/')) {
        $prefix_map[$name] = substr($filepath, 0, $rpos) . '/lib';
      }
    }
    $this->finder->registerPrefixesDeep($prefix_map);
  }
}
