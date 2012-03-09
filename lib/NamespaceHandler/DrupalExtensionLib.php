<?php


class xautoload_NamespaceHandler_DrupalExtensionLib implements xautoload_NamespaceHandler_Interface {

  protected $modules = array();
  protected $system;

  function __construct($system) {
    $this->system = $system;
  }

  /**
   * Find the file for a class that in PSR-0 or PEAR would be in
   * $psr_0_root . '/' . $path_prefix_symbolic . $path_suffix
   */
  function findFile($api, $path_prefix_symbolic, $path_suffix) {
    if (FALSE !== $pos = strpos($path_suffix, DIRECTORY_SEPARATOR)) {
      $module = substr($path_suffix, 0, $pos);
      if (!isset($this->modules[$module])) {
        if ($this->system->module_exists($module)) {
          $module_dir = $this->system->drupal_get_path('module', $module);
          $this->modules[$module] = $this->_moduleClassesDir($module, $module_dir, $path_prefix_symbolic);
        }
        else {
          $this->modules[$module] = FALSE;
        }
      }
      if (!empty($this->modules[$module])) {
        $path = $this->modules[$module] . substr($path_suffix, $pos + 1);
        if ($api->suggestFile($path)) {
          return TRUE;
        }
      }
    }
  }

  protected function _moduleClassesDir($module, $module_dir, $path_prefix_symbolic) {
    return $module_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  }
}
