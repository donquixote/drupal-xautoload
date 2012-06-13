<?php


class xautoload_NamespaceHandler_DrupalExtensionLib implements xautoload_NamespaceHandler_Interface {

  protected $extensions = array();
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
      $name = substr($path_suffix, 0, $pos);
      if (!isset($this->extensions[$name])) {
        if ($this->system->extensionExists($name)) {
          $name_dir = $this->system->getExtensionPath($name);
          $this->extensions[$name] = $this->_extensionClassesDir($name, $name_dir, $path_prefix_symbolic);
        }
        else {
          $this->extensions[$name] = FALSE;
        }
      }
      if (!empty($this->extensions[$name])) {
        $path = $this->extensions[$name] . substr($path_suffix, $pos + 1);
        if ($api->suggestFile($path)) {
          return TRUE;
        }
      }
    }
  }

  protected function _extensionClassesDir($name, $extension_dir, $path_prefix_symbolic) {
    return $extension_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  }
}
