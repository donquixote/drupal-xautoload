<?php


namespace Drupal\xautoload\Tests\Example;


use Drupal\xautoload\Tests\AbstractDrupalBootTest;
use Drupal\xautoload\Tests\Filesystem\VirtualFilesystem;

class ExampleModules extends AbstractExampleModules {

  /**
   * Sets up virtual class files for example modules.
   *
   * @param AbstractDrupalBootTest $testCase
   * @param VirtualFilesystem $filesystem
   *
   * @throws \Exception
   */
  protected function setupExampleModuleClassFiles(AbstractDrupalBootTest $testCase, VirtualFilesystem $filesystem) {
    // Create virtual class files.
    $filesystem->addClass(
      'test://modules/testmod_psr0/lib/Drupal/testmod_psr0/Foo.php',
      'Drupal\testmod_psr0\Foo');
    $filesystem->addClass(
      'test://modules/testmod_psr4_custom/psr4/Foo.php',
      'Drupal\testmod_psr4_custom\Foo');
    $filesystem->addClass(
      'test://modules/testmod_psr4_src/src/Foo.php',
      'Drupal\testmod_psr4_src\Foo');
    $filesystem->addClass(
      'test://modules/testmod_pearflat/lib/Foo.php',
      'testmod_pearflat_Foo');

    $testCase->assertTrue(
      file_exists('test://modules/testmod_psr0/lib/Drupal/testmod_psr0/Foo.php'),
      'Stream wrapper file exists.');
  }

  /**
   * Sets up virtual *.module and *.install files.
   *
   * @throws \Exception
   */
  protected function setupExampleModuleFiles(VirtualFilesystem $filesystem) {
    $special = array();

    $special['testmod_psr4_custom']['module'] = <<<EOT

xautoload()->main->registerModulePsr4('test://modules/testmod_psr4_custom/testmod_psr4_custom.module', 'psr4');

EOT;

    foreach ($this->getExampleClasses() as $extension => $class) {
      $modulePhp = '<?php';
      if (isset($special[$extension]['module'])) {
        $modulePhp .= $special[$extension]['module'];
      }
      $modulePhp .= <<<EOT

function {$extension}_init() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new $class;
}

function {$extension}_modules_enabled() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new $class;
}

function {$extension}_watchdog() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new $class;
}

EOT;
      $filesystem->addPhpFile("test://modules/$extension/$extension.module", $modulePhp, TRUE);

      $installPhp = '<?php';
      if (isset($special[$extension]['install'])) {
        $installPhp .= $special[$extension]['install'];
      }
      $installPhp .= <<<EOT

function {$extension}_enable() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new $class;
}

function {$extension}_install() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new $class;
}

function {$extension}_schema() {
  \\Drupal\\xautoload\\Tests\\Util\\StaticCallLog::addCall();
  new $class;
}
EOT;
      $filesystem->addPhpFile("test://modules/$extension/$extension.install", $installPhp, TRUE);
    }
  }

  /**
   * @return string[]
   */
  public function getAvailableExtensions() {
    return array_fill_keys(array(
        'system', 'xautoload', 'libraries',
        'testmod_pearflat', 'testmod_psr0', 'testmod_psr4_custom', 'testmod_psr4_src',
      ), 'module');
  }

  /**
   * @return string[]
   */
  public function getExampleClasses() {
    return array(
      'testmod_pearflat' => 'testmod_pearflat_Foo',
      'testmod_psr0' => 'Drupal\testmod_psr0\Foo',
      'testmod_psr4_custom' => 'Drupal\testmod_psr4_custom\Foo',
      'testmod_psr4_src' => 'Drupal\testmod_psr4_src\Foo',
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
    if (0 === strpos($name, 'testmod')) {
      $info['dependencies'][] = 'xautoload';
    }
    return $info;
  }

}
