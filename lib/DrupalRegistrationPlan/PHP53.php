<?php

class xautoload_DrupalRegistrationPlan_PHP53 extends xautoload_DrupalRegistrationPlan_PHP52 {

  protected function registerExtensionFilepaths($extension_filepaths) {
    $prefix_map = array();
    $namespace_map = array();
    foreach ($extension_filepaths as $name => $filepath) {
      if (FALSE !== $rpos = strrpos($filepath, '/')) {
        $extension_dir = substr($filepath, 0, $rpos);
        $prefix_map[$name] = $extension_dir . '/lib';
        $namespace_map['Drupal\\' . $name] = $extension_dir . '/lib/Drupal/' . $name;
      }
    }
    $this->finder->registerPrefixesDeep($prefix_map);
    $this->finder->registerNamespacesDeep($namespace_map);
  }
}
