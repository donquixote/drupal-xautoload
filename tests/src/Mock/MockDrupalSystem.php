<?php

namespace Drupal\xautoload\Tests\Mock;

use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;

class MockDrupalSystem implements DrupalSystemInterface {

  /**
   * @var array
   */
  protected $variables;

  /**
   * @var string[]
   */
  protected $activeExtensions;

  /**
   * @param array $variables
   * @param string[] $active_extensions
   */
  function __construct(array $variables, array $active_extensions) {
    $this->variables = $variables;
    $this->activeExtensions = $active_extensions;
  }

  /**
   * {@inheritdoc}
   */
  function variableGet($name, $default = NULL) {
    return isset($this->variables[$name])
      ? $this->variables[$name]
      : $default;
  }

  /**
   * {@inheritdoc}
   */
  function drupalGetFilename($type, $name) {
    // Simply assume that everything is a module.
    return "test://modules/$name/$name.module";
  }

  /**
   * {@inheritdoc}
   */
  function getExtensionTypes($extension_names) {
    // Simply assume that everything is a module.
    return array_fill_keys($extension_names, 'module');
  }

  /**
   * {@inheritdoc}
   */
  function getActiveExtensions() {
    return $this->activeExtensions;
  }

  /**
   * Wrapper for variable_set()
   *
   * @param string $name
   * @param mixed $value
   */
  function variableSet($name, $value) {
    // TODO: Implement variableSet() method.
  }

  /**
   * @see drupal_get_path()
   *
   * @param string $type
   * @param string $name
   *
   * @return string
   */
  function drupalGetPath($type, $name) {
    // TODO: Implement drupalGetPath() method.
  }

  /**
   * Wrapper for module_list()
   *
   * @return array
   */
  function moduleList() {
    // TODO: Implement moduleList() method.
  }

  /**
   * Wrapper for module_implements()
   *
   * @param string $hook
   *
   * @return array[]
   */
  function moduleImplements($hook) {
    // TODO: Implement moduleImplements() method.
  }

  /**
   * @see libraries_info()
   *
   * @return mixed
   */
  function getLibrariesInfo() {
    // TODO: Implement getLibrariesInfo() method.
  }

  /**
   * @see libraries_get_path()
   *
   * @param string $name
   *   Name of the library.
   *
   * @return string|false
   */
  function librariesGetPath($name) {
    // TODO: Implement librariesGetPath() method.
  }

  /**
   * Called from xautoload_install() to set the module weight.
   *
   * @param int $weight
   *   New module weight for xautoload.
   */
  public function installSetModuleWeight($weight) {
    // TODO: Implement installSetModuleWeight() method.
  }
}
