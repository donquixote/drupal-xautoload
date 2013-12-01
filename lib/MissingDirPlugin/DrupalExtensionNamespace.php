<?php

class xautoload_MissingDirPlugin_DrupalExtensionNamespace extends xautoload_MissingDirPlugin_DrupalExtensionAbstract {

  /**
   * @param string $path_fragment
   * @return string
   */
  function alternativeDir($path_fragment) {
    $extension = substr($path_fragment, 7, -1);
    if ($path = drupal_get_path($this->type, $extension)) {
      if ($this->shallow) {
        return $path . '/lib/';
      }
      else {
        return $path . '/lib/Drupal/' . $extension . '/';
      }
    }
  }
}
