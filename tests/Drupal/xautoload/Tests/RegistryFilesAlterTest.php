<?php

namespace Drupal\xautoload\Tests;

class RegistryFilesAlterTest extends \PHPUnit_Framework_TestCase {

  public static function getInfo() {
    return array(
      'name' => 'X Autoload RegistryFilesAlterTest',
      'description' => 'Unit test for hook_registry_files_alter() wildcard replacement.',
      'group' => 'X Autoload',
    );
  }

  function setUp() {
    parent::setUp();
  }

  /**
   * Test hook_registry_files_alter() wildcard replacement.
   */
  public function testRegistryFilesAlter() {

    $files_relative = array(
      'foo/bar.inc',
      'handlers/*.inc',
      'modules/*/**/*.inc',
      'tests/**/*.test',
      'misc/**',
    );

    $files = array();
    foreach ($files_relative as $file) {
      $file = dirname(dirname(dirname(__DIR__))) . '/fixtures/RegistryFilesAlter/' . $file;
      $files[$file] = array('module' => 'views', 'weight' => 0);
    }

    // The class file is loaded using the regular uncached xautoload autoload.
    $rec_scan = new \xautoload_RegistryWildcard_RecursiveScan($files);

    foreach ($files as $path => $file) {
      $rec_scan->check($path, $file);
    }

    // The order of scandir() cannot be predicted, therefore only the sorted
    // list of files is being compared here.
    ksort($files);

    $expected = array (
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/foo/bar.inc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/handlers/bar.inc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/handlers/foo.inc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/misc/abc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/misc/foo.bar' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/misc/sub/xyz' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/modules/sub/foo.inc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/modules/sub/sub/foo.inc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/modules/sub/sub/sub/foo.inc' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/tests/foo.test' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/tests/sub/foo.test' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/tests/sub/sub/foo.test' => array (
        'module' => 'views',
        'weight' => 0,
      ),
      '/home/lemonhead/projects/d7/git-modules/xautoload/tests/fixtures/RegistryFilesAlter/tests/sub/sub/sub/foo.test' => array (
        'module' => 'views',
        'weight' => 0,
      ),
    );

    $this->assertEquals($expected, $files);
  }
} 