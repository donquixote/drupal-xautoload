<?php


class xautoload_NamespaceHandler_DrupalExtensionLibPSR0 extends xautoload_NamespaceHandler_DrupalModuleLib {

  protected function _moduleClassesDir($module, $module_dir, $path_prefix_symbolic) {
    return $module_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $path_prefix_symbolic;
  }
}
