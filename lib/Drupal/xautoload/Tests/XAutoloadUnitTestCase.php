<?php

namespace Drupal\xautoload\Tests;

use Drupal\xautoload\Util;

class XAutoloadUnitTestCase extends \DrupalUnitTestCase {

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'X Autoload unit test',
      'description' => 'Test the xautoload class finder.',
      'group' => 'X Autoload',
    );
  }

  function setUp() {

    // drupal_load('module', 'xautoload') would register namespaces for all
    // enabled modules, which is not intended for this unit test.
    // Instead, we just include xautoload.early.inc.
    require_once __DIR__ . '/../../../../xautoload.early.lib.inc';
    _xautoload_register();

    // Do the regular setUp().
    parent::setUp();
  }

  function testAutoloadStackOrder() {
    $expected = array(
      'xautoload_ClassFinder_NamespaceOrPrefix->loadClass()',
      'drupal_autoload_class',
      'drupal_autoload_interface',
      '_simpletest_autoload_psr0',
    );

    foreach (spl_autoload_functions() as $index => $callback) {
      $str = Util::callbackToString($callback);
      if (!isset($expected[$index])) {
        $this->fail("Autoload callback at index $index must be empty instead of $str.");
      }
      else {
        $expected_str = $expected[$index];
        if ($expected_str === $str) {
          $this->pass("Autoload callback at index $index must be $expected_str.");
        }
        else {
          $this->fail("Autoload callback at index $index must be $expected_str instead of $str.");
        }
      }
    }
  }
}
