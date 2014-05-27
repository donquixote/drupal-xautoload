<?php


namespace Drupal\xautoload\Tests\DrupalScenario;


use Drupal\xautoload\Tests\DrupalBootTest\AbstractDrupalBootTest;
use Drupal\xautoload\Tests\Filesystem\VirtualFilesystem;
use Drupal\xautoload\Tests\VirtualDrupal\DrupalScenarioInterface;
use Drupal\xautoload\Tests\VirtualDrupal\PureFunctions;

abstract class AbstractDrupalScenario implements DrupalScenarioInterface {

  /**
   * Sets up virtual files to simulate a Drupal environment.
   *
   * @param AbstractDrupalBootTest $testCase
   * @param \Drupal\xautoload\Tests\Filesystem\VirtualFilesystem $filesystem
   */
  public function setupVirtualFiles(AbstractDrupalBootTest $testCase, VirtualFilesystem $filesystem) {
    $this->setupExampleModuleClassFiles($testCase, $filesystem);
    $this->setupExampleModuleFiles($filesystem);
    $phpNothing = <<<EOT
<?php
// Nothing special here.
EOT;
    $filesystem->addPhpFile('test://modules/system/system.module', $phpNothing, TRUE);
    $filesystem->addPhpFile('test://modules/system/system.install', $phpNothing, TRUE);
    $filesystem->addPhpFile('test://modules/libraries/libraries.module', $phpNothing, TRUE);
    $filesystem->addPhpFile('test://modules/libraries/libraries.install', $phpNothing, TRUE);
  }

  /**
   * Sets up virtual class files for example modules.
   *
   * @param AbstractDrupalBootTest $testCase
   * @param VirtualFilesystem $filesystem
   */
  abstract protected function setupExampleModuleClassFiles(AbstractDrupalBootTest $testCase, VirtualFilesystem $filesystem);

  /**
   * Sets up virtual *.module and *.install files.
   *
   * @throws \Exception
   */
  abstract protected function setupExampleModuleFiles(VirtualFilesystem $filesystem);

  /**
   * Replicates drupal_system_listing('/^' . DRUPAL_PHP_FUNCTION_PATTERN . '\.module$/', 'modules', 'name', 0)
   *
   * @see drupal_system_listing()
   *
   * @return object[]
   */
  public function drupalSystemListingModules() {
    $modules = array();
    foreach ($this->getAvailableExtensions() as $name => $type) {
      if ('module' !== $type) {
        continue;
      }
      $modules[$name] = (object)array(
        'uri' => $this->getExtensionFilename($type, $name),
        'filename' => $name . '.module',
        'name' => $name,
      );
    }
    return $modules;
  }

  /**
   * @return string[]
   *   Extension types by name.
   */
  abstract protected function getAvailableExtensions();

  /**
   * @return true[]
   */
  public function getBootstrapModules() {
    $bootstrap_modules = array();
    foreach ($this->discoverModuleFilenames('module') as $name => $filename) {
      $php = file_get_contents($filename);
      foreach (PureFunctions::bootstrapHooks() as $hook) {
        if (FALSE !== strpos($php, 'function ' . $name . '_' . $hook)) {
          $bootstrap_modules[$name] = TRUE;
          break;
        }
      }
    }
    return $bootstrap_modules;
  }

  /**
   * @param \Drupal\xautoload\Tests\DrupalBootTest\AbstractDrupalBootTest $testCase
   */
  public function assertLoadExampleClasses(AbstractDrupalBootTest $testCase) {
    foreach ($this->getExampleClasses() as $class) {
      $testCase->assertLoadClass($class);
    }
  }

  /**
   * @return array[]
   */
  abstract public function getExampleClasses();

  /**
   * @param string $type
   *   E.g. 'module'
   *
   * @return string[]
   */
  function discoverModuleFilenames($type) {
    $filenames = array();
    foreach ($this->getAvailableExtensions() as $name => $itemType) {
      if ($type !== $itemType) {
        continue;
      }
      $filenames[$name] = $this->getExtensionFilename($type, $name);
    }
    return $filenames;
  }

  /**
   * @param string $type
   * @param string $name
   *
   * @return string
   */
  public function getExtensionFilename($type, $name) {
    if ('xautoload' === $name) {
      return dirname(dirname(dirname(__DIR__))) . '/xautoload.module';
    }
    switch ($type) {
      case 'theme':
        return "test://themes/$name/$name.info";
      case 'module':
      default:
        return "test://modules/$name/$name.module";
    }
  }

} 
