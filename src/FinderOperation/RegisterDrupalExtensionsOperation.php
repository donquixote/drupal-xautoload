<?php


namespace Drupal\xautoload\FinderOperation;


use Drupal\xautoload\Adapter\ClassFinderAdapter;
use Drupal\xautoload\Adapter\DrupalExtensionAdapter;
use Drupal\xautoload\Discovery\ClassMapGenerator;
use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;
use Drupal\xautoload\Tests\Util\StaticCallLog;

class RegisterDrupalExtensionsOperation implements FinderOperationInterface {

  /**
   * @var DrupalSystemInterface
   */
  private $system;

  /**
   * The finder object, once initialized, or NULL before that.
   *
   * @var \Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface|null
   */
  private $finder = NULL;

  /**
   * @var DrupalExtensionAdapter
   */
  private $adapter = NULL;

  /**
   * @var string[]|null
   */
  private $registeredExtensions = NULL;

  /**
   * Modules that were added manually with xautoload()->registerModulePsr4()
   *
   * @var array[]
   */
  private $modulesManuallyPsr4 = array();

  /**
   * Modules that were added manually with xautoload()->registerModulePsr4()
   *
   * @var array
   */
  private $modulesManually = array();

  /**
   * Whether we are in a time in the request where hook_xautoload() can run.
   *
   * @var bool
   */
  private $hookXautoloadReady = FALSE;

  /**
   * Whether hook_xautoload() has already run.
   *
   * This will switch to TRUE in $this->runHookXautoload()
   * It may switch to FALSE again in $this->checkNewExtensions()
   *
   * @var bool
   */
  private $hookXautoloadDone = FALSE;

  /**
   * @param DrupalSystemInterface $system
   */
  public function __construct(DrupalSystemInterface $system) {
    $this->system = $system;
  }

  /**
   * {@inheritdoc}
   */
  public function operateOnFinder($finder, $adapter) {
    # StaticCallLog::addCall();
    $this->registeredExtensions = $this->system->getActiveExtensions();
    $adapter->registerExtensions($this->registeredExtensions);
    $this->finder = $finder;
    $this->adapter = $adapter;
    $this->attemptHookXautoload();
  }

  /**
   * Checks if new extensions have been enabled, and registers them.
   *
   * This is called from xautoload_module_implements_alter(), which is called
   * whenever a new module is enabled, but also some calls we need to ignore.
   */
  public function checkNewExtensions() {
    # StaticCallLog::addCall();
    if (NULL === $this->finder) {
      // The entire thing is not initialized yet.
      // Postpone until operateOnFinder()
      return;
    }
    $activeExtensions = $this->system->getActiveExtensions();
    if ($activeExtensions === $this->registeredExtensions) {
      // Nothing actually changed. False alarm.
      return;
    }
    $newExtensions = array();
    foreach ($activeExtensions as $extension => $type) {
      if (!isset($this->registeredExtensions[$extension])) {
        $newExtensions[$extension] = $type;
      }
    }
    if (empty($newExtensions)) {
      // No new extensions. False alarm.
      return;
    }
    $this->welcomeNewExtensions($newExtensions);
  }

  /**
   * New extensions were enabled/installed.
   *
   * @param string[] $newExtensions
   *   Extension type by name.
   */
  private function welcomeNewExtensions(array $newExtensions) {
    # StaticCallLog::addCall();

    // Register the default PSR-0 and PSR-4 and PEAR-FLAT mappings for the new
    // extensions.
    $this->adapter->registerExtensions($newExtensions);

    // Make sure hook_xautoload() runs again if it has already run.
    $this->hookXautoloadDone = FALSE;
    // If xautoload was just enabled, its hook_init() hook has not run, so
    // we must set hook_xautoload() ready.
    $this->hookXautoloadReady = TRUE;
    $this->attemptHookXautoload();
  }

  /**
   * Switches to a phase where hook_xautoload() is ready.
   *
   * This is typically
   */
  public function setHookXautoloadReady() {
    # StaticCallLog::addCall();
    # $this->checkNewExtensions();
    $this->hookXautoloadReady = TRUE;
    $this->attemptHookXautoload();
  }

  /**
   * Runs hook_xautolaod(), if
   * - it is ready to run
   * - it did not "done".
   * - the finder has been initialized.
   */
  private function attemptHookXautoload() {
    if (!$this->hookXautoloadReady) {
      # StaticCallLog::addCall();
      return;
    }
    if ($this->hookXautoloadDone) {
      # StaticCallLog::addCall();
      return;
    }
    if (NULL === $this->finder) {
      # StaticCallLog::addCall();
      return;
    }
    # StaticCallLog::addCall();
    $this->runHookXautoload();
    $this->hookXautoloadDone = TRUE;
  }

  /**
   * Manually registers a module even though it might not be enabled yet, or it
   * might never be enabled at all.
   *
   * @param string $module
   *   The module name
   * @param string $dir
   *   The module directory
   */
  public function addModuleInPath($module, $dir) {
    $dir = rtrim($dir, '/') . '/';
    if (NULL === $this->finder || NULL === $this->adapter) {
      // Queue this up until the finder is initialized.
    }
    else {
      // Register directly.
      $this->adapter->registerExtension($module, 'module', $dir);
    }
  }

  /**
   * Manually registers a module (as PSR-4), even though it might not be enabled
   * yet, or it might never be enabled at all.
   *
   * @param string $module
   *   The module name
   * @param string $dir
   *   The module directory
   * @param $subdir
   *   The subdirectory for PSR-4, e.g. 'src' or 'lib' or 'psr4'.
   */
  public function addModulePsr4($module, $dir, $subdir) {
    $dir = rtrim($dir, '/') . '/';
    $subdir = ltrim($subdir, '/');
    if (NULL === $this->finder) {
      // Queue this up until the finder is initialized.
      $this->modulesManuallyPsr4[$module] = array($dir, $subdir);
    }
    else {
      // Register directly.
      $this->finder->addPsr4("Drupal\\$module\\", $dir . $subdir);
    }
  }

  /**
   * Runs hook_xautoload() on all enabled modules.
   *
   * This may occur multiple times in a request, if new modules are enabled.
   */
  private function runHookXautoload() {
    # StaticCallLog::addCall();
    // Let other modules register stuff to the finder via hook_xautoload().
    $classmap_generator = new ClassMapGenerator();
    $adapter = new ClassFinderAdapter($this->finder, $classmap_generator);
    $api = new \xautoload_InjectedAPI_hookXautoload($adapter, '');
    foreach ($this->system->moduleImplements('xautoload') as $module) {
      $api->setExtensionDir($dir = $this->system->drupalGetPath('module', $module));
      $f = $module . '_xautoload';
      $f($api, $dir);
    }
  }
} 
