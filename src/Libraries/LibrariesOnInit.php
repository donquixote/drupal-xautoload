<?php


namespace Drupal\xautoload\Libraries;


use Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface;
use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;
use Drupal\xautoload\Phases\PhaseObserverInterface;


/**
 * Registers autoload mappings from all libraries on hook_init(), or after the
 * first cache miss.
 */
class LibrariesOnInit implements PhaseObserverInterface {

  /**
   * @var DrupalSystemInterface
   */
  private $system;

  /**
   * @var ExtendedClassFinderInterface
   */
  private $finder;

  /**
   * @param DrupalSystemInterface $system
   */
  function __construct(DrupalSystemInterface $system) {
    $this->system = $system;
  }

  /**
   * Wake up after a cache fail.
   *
   * @param ExtendedClassFinderInterface $finder
   * @param string[] $extensions
   *   Extension type by extension name.
   */
  public function wakeUp(ExtendedClassFinderInterface $finder, array $extensions) {
    $this->finder = $finder;
  }

  /**
   * Enter the boot phase of the request, where all bootstrap module files are included.
   */
  public function enterBootPhase() {
    // Nothing.
  }

  /**
   * Enter the main phase of the request, where all module files are included.
   */
  public function enterMainPhase() {
    $this->registerAllLibraries();
  }

  /**
   * React to new extensions that were just enabled.
   *
   * @param string $name
   * @param string $type
   */
  public function welcomeNewExtension($name, $type) {
    // Nothing.
  }

  /**
   * React to xautoload_modules_enabled()
   *
   * @param string[] $modules
   *   New module names.
   */
  public function modulesEnabled($modules) {
    $this->system->drupalStaticReset('libraries_info');
    $this->system->cacheClearAll('xautoload_libraries_info', 'cache');
    $this->registerAllLibraries();
  }

  /**
   * Registers all libraries that have an "xautoload" setting.
   */
  private function registerAllLibraries() {
    # StaticCallLog::addCall();
    $adapter = \xautoload_InjectedAPI_hookXautoload::create($this->finder, '');
    foreach ($info = $this->getLibrariesXautoloadInfo() as $name => $pathAndCallback) {
      list($path, $callback) = $pathAndCallback;
      if (!is_callable($callback)) {
        continue;
      }
      if (!is_dir($path)) {
        continue;
      }
      $adapter->setExtensionDir($path);
      call_user_func($callback, $adapter, $path);
    }
  }

  /**
   * @return array[]
   */
  private function getLibrariesXautoloadInfo() {
    # StaticCallLog::addCall();
    $cached = $this->system->cacheGet('xautoload_libraries_info');
    if (FALSE !== $cached) {
      return $cached->data;
    }
    $info = $this->buildLibrariesXautoloadInfo();
    $this->system->cacheSet('xautoload_libraries_info', $info);
    return $info;
  }

  /**
   * @return array[]
   */
  private function buildLibrariesXautoloadInfo() {
    # StaticCallLog::addCall();
    // @todo Reset drupal_static('libraries') ?
    $all = array();
    foreach ($this->system->getLibrariesInfo() as $name => $info) {
      if (!isset($info['xautoload'])) {
        continue;
      }
      $callback = $info['xautoload'];
      if (!is_callable($callback)) {
        continue;
      }
      $path = $this->system->librariesGetPath($name);
      if (FALSE === $path) {
        continue;
      }
      $all[$name] = array($path, $callback);
    }
    return $all;
  }

}
