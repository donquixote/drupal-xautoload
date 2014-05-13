<?php


namespace Drupal\xautoload\Phases;


use Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface;

interface PhaseObserverInterface {

  /**
   * Wake up after a cache fail.
   *
   * @param ExtendedClassFinderInterface $finder
   * @param string[] $extensions
   *   Extension type by extension name.
   */
  public function wakeUp(ExtendedClassFinderInterface $finder, array $extensions);

  /**
   * Enter the boot phase of the request, where all bootstrap module files are included.
   */
  public function enterBootPhase();

  /**
   * Enter the main phase of the request, where all module files are included.
   */
  public function enterMainPhase();

  /**
   * React to new extensions that were just enabled.
   *
   * @param string $name
   * @param string $type
   */
  public function welcomeNewExtension($name, $type);

  /**
   * React to xautoload_modules_enabled()
   *
   * @param string[] $modules
   *   New module names.
   */
  public function modulesEnabled($modules);
}
