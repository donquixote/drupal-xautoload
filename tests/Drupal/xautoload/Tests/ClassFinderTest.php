<?php

namespace Drupal\xautoload\Tests;

class ClassFinderTest extends \PHPUnit_Framework_TestCase implements PublicAssertInterface {

  function setUp() {
    parent::setUp();
  }

  //                                                                Test methods
  // ---------------------------------------------------------------------------

  function testNamespaces() {

    // Prepare the class finder.
    $finder = new \xautoload_ClassFinder_NamespaceOrPrefix();
    $finder->registerNamespaceDeep('Drupal\\ex_ample', 'sites/all/modules/contrib/ex_ample/lib');
    $finder->registerNamespaceRoot('Drupal\\ex_ample', 'sites/all/modules/contrib/ex_ample/vendor');

    // Test class finding for 'Drupal\\ex_ample\\Abc_Def'.
    $this->assertFinderSuggestions($finder, 'Drupal\\ex_ample\\Abc_Def', array(
      // Class finder is expected to suggest these files, in the exact order,
      // until one of them is accepted.
      'sites/all/modules/contrib/ex_ample/lib/Abc/Def.php',
      'sites/all/modules/contrib/ex_ample/vendor/Drupal/ex_ample/Abc/Def.php',
    ));
  }

  function testPrefixes() {

    // Prepare the class finder.
    $finder = new \xautoload_ClassFinder_NamespaceOrPrefix();
    $finder->registerPrefixDeep('ex_ample', 'sites/all/modules/contrib/ex_ample/lib');
    $finder->registerPrefixRoot('ex_ample', 'sites/all/modules/contrib/ex_ample/vendor');

    // Test class finding for 'ex_ample_Abc_Def'.
    $this->assertFinderSuggestions($finder, 'ex_ample_Abc_Def', array(
      // Class finder is expected to suggest these files, in the exact order,
      // until one of them is accepted.
      'sites/all/modules/contrib/ex_ample/lib/Abc/Def.php',
      'sites/all/modules/contrib/ex_ample/vendor/ex/ample/Abc/Def.php',
    ));
  }

  //                                                           Assertion helpers
  // ---------------------------------------------------------------------------

  /**
   * @param \xautoload_ClassFinder_Interface $finder
   * @param string $class
   * @param string[] $expectedSuggestions
   */
  protected function assertFinderSuggestions($finder, $class, array $expectedSuggestions) {
    for ($iAccept = 0; $iAccept < count($expectedSuggestions); ++$iAccept) {
      $api = new \xautoload_Mock_InjectedAPI_findFile($this, $class, $expectedSuggestions, $iAccept);
      $finder->findFile($api, $class);
      $api->finish();
    }
    $api = new \xautoload_Mock_InjectedAPI_findFile($this, $class, $expectedSuggestions);
    $finder->findFile($api, $class);
    $api->finish();
    $this->assertTrue(TRUE, "Successfully loaded $class");
  }

  /**
   * @param bool $status
   * @param string $message
   *
   * @return bool
   */
  function assertPublic($status, $message) {
    $this->assertTrue($status, $message);
    return $status;
  }
}
