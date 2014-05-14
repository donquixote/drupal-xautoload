<?php

namespace Drupal\xautoload\Tests\Mock;

use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;
use Drupal\xautoload\Tests\VirtualDrupal\DrupalGetFilename;
use Drupal\xautoload\Tests\VirtualDrupal\HookSystem;
use Drupal\xautoload\Tests\VirtualDrupal\LibrariesInfo;
use Drupal\xautoload\Tests\VirtualDrupal\ModuleList;
use Drupal\xautoload\Tests\VirtualDrupal\PureFunctions;
use Drupal\xautoload\Tests\VirtualDrupal\SystemListReset;
use Drupal\xautoload\Tests\VirtualDrupal\SystemTable;

class MockDrupalSystem implements DrupalSystemInterface {

  /**
   * @var array
   */
  private $variables = array();

  /**
   * @var HookSystem
   */
  private $hookSystem;

  /**
   * @var SystemTable
   */
  private $systemTable;

  /**
   * @var ModuleList
   */
  private $moduleList;

  /**
   * @var DrupalGetFilename
   */
  private $drupalGetFilename;

  /**
   * @var LibrariesInfo
   */
  private $librariesInfo;

  /**
   * @var SystemListReset
   */
  private $systemListReset;

  /**
   * @param SystemTable $systemTable
   * @param ModuleList $moduleList
   * @param HookSystem $hookSystem
   * @param DrupalGetFilename $drupalGetFilename
   * @param LibrariesInfo $librariesInfo
   * @param SystemListReset $systemListReset
   */
  function __construct(
    SystemTable $systemTable,
    ModuleList $moduleList,
    HookSystem $hookSystem,
    DrupalGetFilename $drupalGetFilename,
    LibrariesInfo $librariesInfo,
    SystemListReset $systemListReset
  ) {
    $this->systemTable = $systemTable;
    $this->moduleList = $moduleList;
    $this->hookSystem = $hookSystem;
    $this->drupalGetFilename = $drupalGetFilename;
    $this->librariesInfo = $librariesInfo;
    $this->systemListReset = $systemListReset;
  }

  /**
   * {@inheritdoc}
   */
  function variableSet($name, $value) {
    $this->variables[$name] = $value;
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
    return $this->drupalGetFilename->drupalGetFilename($type, $name);
  }

  /**
   * {@inheritdoc}
   */
  function drupalGetPath($type, $name) {
    return $this->drupalGetFilename->drupalGetPath($type, $name);
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
    return $this->systemTable->getActiveExtensions();
  }

  /**
   * Replicates module_list()
   *
   * @param bool $refresh
   * @param bool $bootstrap_refresh
   * @param bool $sort
   *
   * @return string[]
   */
  function moduleList($refresh = FALSE, $bootstrap_refresh = FALSE, $sort = FALSE) {
    return $this->moduleList->moduleList($refresh, $bootstrap_refresh, $sort);
  }

  /**
   * @see module_invoke()
   *
   * @param string $module
   * @param string $hook
   *
   * @return mixed
   *
   * @throws \Exception
   */
  function moduleInvoke($module, $hook) {
    $args = func_get_args();
    switch (count($args)) {
      case 2:
        return PureFunctions::moduleInvoke($module, $hook);
      case 3:
        return PureFunctions::moduleInvoke($module, $hook, $args[2]);
      case 4:
        return PureFunctions::moduleInvoke($module, $hook, $args[2], $args[3]);
      default:
        throw new \Exception("More arguments than expected.");
    }
  }

  /**
   * @param string $hook
   */
  function moduleInvokeAll($hook) {
    $args = func_get_args();
    call_user_func_array(array($this->hookSystem, 'moduleInvokeAll'), $args);
  }

  /**
   * @param string $hook
   *
   * @throws \Exception
   * @return array
   */
  function moduleImplements($hook) {
    return $this->hookSystem->moduleImplements($hook);
  }

  /**
   * @param string $hook
   * @param mixed $data
   */
  function drupalAlter($hook, &$data) {
    $args = func_get_args();
    assert($hook === array_shift($args));
    assert($data === array_shift($args));
    while (count($args) < 3) {
      $args[] = NULL;
    }
    $this->hookSystem->drupalAlter($hook, $data, $args[0], $args[1], $args[2]);
  }

  /**
   * Replicates module_load_include()
   *
   * @param string $type
   * @param string $module
   * @param string|null $name
   *
   * @return bool|string
   */
  function moduleLoadInclude($type, $module, $name = NULL) {
    if (!isset($name)) {
      $name = $module;
    }
    $file = $this->drupalGetPath('module', $module) . "/$name.$type";
    if (is_file($file)) {
      require_once $file;
      return $file;
    }
    return FALSE;
  }

  /**
   * Resets the module_implements() cache.
   */
  public function resetModuleImplementsCache() {
    $this->hookSystem->moduleImplementsReset();
  }

  /**
   * @see libraries_info()
   *
   * @return mixed
   */
  function getLibrariesInfo() {
    $this->librariesInfo->resetLibrariesInfo();
    return $this->librariesInfo->getLibrariesInfo();
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
    return $this->librariesInfo->librariesGetPath($name);
  }

  /**
   * Called from xautoload_install() to set the module weight.
   *
   * @param int $weight
   *   New module weight for xautoload.
   */
  public function installSetModuleWeight($weight) {
    $this->systemTable->moduleSetWeight('xautoload', $weight);
    $this->systemListReset->systemListReset();
  }
}
