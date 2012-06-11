<?php


class xautoload_Mock_DrupalExtensionSystem {

  protected $extensions;

  function module_exists($module) {
    return isset($this->extensions[$module]);
  }

  function drupal_get_path($type, $name) {
    $info = @$this->extensions[$name];
    if ($info && $info['type'] === $type) {
      return $info['path'];
    }
  }

  function addExtension($type, $name, $path) {
    $this->extensions[$name] = array(
      'type' => $type,
      'path' => $path,
    );
  }

  function addModule($name, $path) {
    $this->addExtension('module', $name, $path);
  }

  function addTheme($name, $path) {
    $this->addExtension('theme', $name, $path);
  }
}
