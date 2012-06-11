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
    $pos = 0;
    while (TRUE) {
      $pos = strpos($path_suffix, DIRECTORY_SEPARATOR);
      if (FALSE === $pos) {
        return FALSE;
      }
      $char = @$path_suffix{$pos + 1};
      if (isset($char) && "$char" === strtoupper($char)) {
        // We found a '_' followed by an uppercase character.
        break;
      }
      // We hit a normal '_' within a module name.
      $path_suffix[$pos] = '_';
    }
    if (FALSE !== $pos) {
      $module = substr($path_suffix, 0, $pos);
      $this->_initModule($module, $path_prefix_symbolic);
      if (!empty($this->modules[$module])) {
        $path = $this->modules[$module] . substr($path_suffix, $pos + 1);
        if ($api->suggestFile($path)) {
          return TRUE;
        }
      }
    }
  }

  protected function _initModule($module, $path_prefix_symbolic) {
    if (!isset($this->modules[$module])) {
      if ($this->system->module_exists($module)) {
        $module_dir = $this->system->drupal_get_path('module', $module);
        $this->modules[$module] = $this->_moduleClassesDir($module, $module_dir, $path_prefix_symbolic);
      }
      else {
        $this->modules[$module] = FALSE;
      }
    }
  }

  protected function _moduleClassesDir($module, $module_dir, $path_prefix_symbolic) {
    return $module_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  }
}
