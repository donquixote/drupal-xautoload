<?php


namespace Drupal\xautoload\Tests\Example;


use Drupal\xautoload\Tests\DrupalBootTest\AbstractDrupalBootTest;
use Drupal\xautoload\Tests\Filesystem\VirtualFilesystem;

/**
 * @see DrupalBootHookTest
 */
class HookTestExampleModules extends AbstractExampleModules {

  /**
   * Sets up virtual class files for example modules.
   *
   * @param \Drupal\xautoload\Tests\DrupalBootTest\AbstractDrupalBootTest $testCase
   * @param VirtualFilesystem $filesystem
   *
   * @throws \Exception
   */
  protected function setupExampleModuleClassFiles(AbstractDrupalBootTest $testCase, VirtualFilesystem $filesystem) {

    $filesystem->addClass(
      'test://modules/testmod/psr4/Foo.php',
      'Drupal\testmod\Foo');

    $filesystem->addClass(
      'test://libraries/testlib/src/Foo.php',
      'Acme\\TestLib\\Foo');

    $testCase->assertTrue(
      file_exists('test://modules/testmod/psr4/Foo.php'),
      'Stream wrapper file exists.');

    $testCase->assertTrue(
      file_exists('test://libraries/testlib/src/Foo.php'),
      'Stream wrapper file exists.');
  }

  /**
   * Sets up virtual *.module and *.install files.
   *
   * @throws \Exception
   */
  protected function setupExampleModuleFiles(VirtualFilesystem $filesystem) {

    $filesystem->addPhpFile('test://modules/testmod/testmod.module', <<<EOT
<?php

function testmod_init() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new \\Drupal\\testmod\\Foo();
  new \\Acme\\TestLib\\Foo();
}

function testmod_modules_enabled() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  # new \\Drupal\\testmod\\Foo();
  # new \\Acme\\TestLib\\Foo();
}

function testmod_watchdog() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  # new \\Drupal\\testmod\\Foo();
  # new \\Acme\\TestLib\\Foo();
}

function testmod_xautoload(\$adapter) {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  \$adapter->addPsr4('Drupal\\testmod\\\\', 'psr4');
  new \\Drupal\\testmod\\Foo();
}

function testmod_libraries_info() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new \\Drupal\\testmod\\Foo();
  return array(
    'testlib' => array(
      'name' => 'Test library',
      'xautoload' => '_testmod_libraries_testlib_xautoload',
    ),
  );
}

function _testmod_libraries_testlib_xautoload(\$adapter) {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  \$adapter->addPsr4('Acme\\TestLib\\\\', 'src');
}

EOT
    );

    $filesystem->addPhpFile('test://modules/testmod/testmod.install', <<<EOT
<?php

function testmod_enable() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  # new \\Drupal\\testmod\\Foo();
}

function testmod_install() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  # new \\Drupal\\testmod\\Foo();
}

function testmod_schema() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  # new \\Drupal\\testmod\\Foo();
}

EOT
    );
  }

  /**
   * @return array[]
   */
  public function getAvailableExtensions() {
    return array_fill_keys(array(
        'system', 'xautoload', 'libraries',
        'testmod',
      ), 'module');
  }

  /**
   * @return string[]
   */
  public function getExampleClasses() {
    return array(
      'testmod' => array(
        'Drupal\\testmod\\Foo',
        'Acme\\TestLib\\Foo',
      ),
    );
  }

  /**
   * Replicates drupal_parse_info_file(dirname($module->uri) . '/' . $module->name . '.info')
   *
   * @see drupal_parse_info_file()
   *
   * @param string $name
   *
   * @return array
   *   Parsed info file contents.
   */
  public function drupalParseInfoFile($name) {
    $info = array('core' => '7.x');
    if ('testmod' === $name) {
      $info['dependencies'][] = 'xautoload';
      $info['dependencies'][] = 'libraries';
    }
    return $info;
  }

}
