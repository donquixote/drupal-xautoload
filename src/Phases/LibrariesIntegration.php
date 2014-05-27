<?php


namespace Drupal\xautoload\Phases;


use Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface;
use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;

class LibrariesIntegration implements PhaseObserverInterface {

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
  public function enterPreMainPhase() {
    // Nothing.
  }

  /**
   * Enter the main phase of the request, where hook_init() fires.
   */
  public function enterMainPhase() {
    $this->initLibrariesIntegration();
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
    // @todo Reset drupal_static('libraries') ?
    $this->initLibrariesIntegration();
  }

  private function initLibrariesIntegration() {
    $adapter = \xautoload_InjectedAPI_hookXautoload::create($this->finder, '');
    foreach ($this->system->getLibrariesInfo() as $name => $info) {
      if (isset($info['xautoload']) && is_callable($callback = $info['xautoload'])) {
        $adapter->setExtensionDir($dir = $this->system->librariesGetPath($name));
        call_user_func($callback, $adapter, $dir);
      }
    }
  }
}
