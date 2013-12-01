<?php

namespace Drupal\xautoload\Tests;

/**
 * @_runTestsInSeparateProcesses
 * @_backupGlobals disabled
 * @_preserveGlobalState disabled
 */
class StreamWrapperClassLoaderTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var DummyFilesystem
   */
  protected $filesystem;

  function setUp() {
    parent::setUp();
    $this->filesystem = StreamWrapper::register('test');
  }

  function tearDown() {
    stream_wrapper_unregister('test');
  }

  //                                                                Test methods
  // ---------------------------------------------------------------------------

  /**
   * @_runInSeparateProcess
   * @_preserveGlobalState disabled
   *
   * @throws \Exception
   */
  function testNamespaces() {

    // Prepare the class finder.
    $finder = new \xautoload_ClassFinder_NamespaceOrPrefix();
    $loader = new \xautoload_ClassLoader_NoCache($finder);

    $finder->registerNamespaceDeep('Drupal\\ex_ample', 'test://base/lib');
    $finder->registerNamespaceRoot('Drupal\\ex_ample', 'test://base/vendor');

    $this->assertLoadClass($loader, 'Drupal\ex_ample\Sub\Foo', 'test://base/lib/Sub/Foo.php');
    $this->assertLoadClass($loader, 'Drupal\ex_ample\Sub\Bar', 'test://base/vendor/Drupal/ex_ample/Sub/Bar.php');
  }

  /**
   * @_runInSeparateProcess
   * @_preserveGlobalState disabled
   */
  function testPrefixes() {

    // Prepare the class finder.
    $finder = new \xautoload_ClassFinder_NamespaceOrPrefix();
    $loader = new \xautoload_ClassLoader_NoCache($finder);

    $finder->registerPrefixDeep('ex_ample', 'test://base/lib');
    $finder->registerPrefixRoot('ex_ample', 'test://base/vendor');

    $this->assertloadClass($loader, 'ex_ample_Sub_Foo', 'test://base/lib/Sub/Foo.php');
    $this->assertloadClass($loader, 'ex_ample_Sub_Bar', 'test://base/vendor/ex/ample/Sub/Bar.php');
  }

  //                                                           Assertion helpers
  // ---------------------------------------------------------------------------

  /**
   * @param \xautoload_ClassLoader_Interface $loader
   * @param string $class
   * @param string $file
   */
  protected function assertLoadClass($loader, $class, $file) {

    // Register the class file in the virtual filesystem.
    $this->filesystem->addClass($file, $class);

    // Check that the class is not already defined.
    $this->assertFalse(class_exists($class, FALSE));

    // Trigger the class loader.
    $loader->loadClass($class);

    // Check that the class is defined after the class loader has done its job.
    $this->assertTrue(class_exists($class, FALSE));
  }
}
