<?php


namespace Drupal\xautoload\Tests\VirtualDrupal;


use Drupal\xautoload\Tests\Mock\MockDrupalSystem;

class DrupalEnvironment {

  /**
   * @var ExampleModulesInterface
   */
  private $exampleModules;

  /**
   * @var \Drupal\xautoload\Tests\Mock\MockDrupalSystem
   */
  private $mockDrupalSystem;

  /**
   * @var SystemTable
   */
  private $systemTable;

  /**
   * @var Cache
   */
  private $cache;

  /**
   * @var DrupalBootstrap
   */
  private $drupalBoot;

  /**
   * @var ModuleEnable
   */
  private $moduleEnable;

  /**
   * @param ExampleModulesInterface $exampleModules
   */
  function __construct(ExampleModulesInterface $exampleModules) {
    $this->exampleModules = $exampleModules;
    $drupalStatic = new DrupalStatic();
    $this->systemTable = new SystemTable();
    $drupalGetFilename = new DrupalGetFilename($this->systemTable, $this->exampleModules);
    $this->cache = new Cache();
    $systemList = new SystemList($this->cache, $this->systemTable, $drupalGetFilename, $drupalStatic);
    $moduleList = new ModuleList($drupalGetFilename, $systemList, $drupalStatic);
    $hookSystem = new HookSystem($drupalStatic, $this->cache, $moduleList);
    $systemBuildModuleData = new SystemBuildModuleData($this->exampleModules, $hookSystem);
    $moduleBuildDependencies = new ModuleBuildDependencies();
    $systemListReset = new SystemListReset($this->cache, $drupalStatic);
    $systemRebuildModuleData = new SystemRebuildModuleData($drupalStatic, $moduleBuildDependencies, $this->systemTable, $systemBuildModuleData, $systemListReset);
    $librariesInfo = new LibrariesInfo($drupalStatic, $hookSystem);
    $this->mockDrupalSystem = new MockDrupalSystem($this->systemTable, $moduleList, $hookSystem, $drupalGetFilename, $librariesInfo, $systemListReset);
    $drupalLoad = new DrupalLoad($drupalGetFilename);
    $this->drupalBoot = new DrupalBootstrap($drupalLoad, $hookSystem, $moduleList);
    $systemUpdateBootstrapStatus = new SystemUpdateBootstrapStatus($hookSystem, $this->systemTable, $systemListReset);
    $this->moduleEnable = new ModuleEnable($drupalGetFilename, $hookSystem, $moduleList, $this->systemTable, $systemListReset, $systemRebuildModuleData, $systemUpdateBootstrapStatus);
  }

  /**
   * @return MockDrupalSystem
   */
  function getMockDrupalSystem() {
    return $this->mockDrupalSystem;
  }

  /**
   * @return Cache
   */
  function getCache() {
    return $this->cache;
  }

  /**
   * @return SystemTable
   */
  function getSystemTable() {
    return $this->systemTable;
  }

  /**
   * Simulates Drupal's \module_enable()
   *
   * @param string[] $module_list
   *   Array of module names.
   * @param bool $enable_dependencies
   *   TRUE, if dependencies should be enabled too.
   *
   * @return bool
   */
  function moduleEnable(array $module_list, $enable_dependencies = TRUE) {
    $this->moduleEnable->moduleEnable($module_list, $enable_dependencies);
  }

  /**
   * Replicates the Drupal bootstrap.
   */
  public function boot() {
    $this->drupalBoot->boot();
  }

  /**
   * Version of systemUpdateBootstrapStatus() with no side effects.
   *
   * @see _system_update_bootstrap_status()
   */
  public function initBootstrapStatus() {
    $bootstrap_modules = $this->exampleModules->getBootstrapModules();
    $this->systemTable->setBootstrapModules($bootstrap_modules);
  }

}
