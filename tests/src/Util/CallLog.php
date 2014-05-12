<?php


namespace Drupal\xautoload\Tests\Util;

/**
 * @see StaticCallLog
 */
class CallLog {

  /**
   * @var array[]
   */
  private $calls = array();

  /**
   * @param array $call
   */
  function addCall(array $call) {
    $this->calls[] = $call;
  }

  /**
   * @return array[]
   */
  function getCalls() {
    return $this->calls;
  }

  /**
   * @param \PHPUnit_Framework_TestCase $testCase
   * @param array[] $expectedCalls
   */
  function assertCalls(\PHPUnit_Framework_TestCase $testCase, array $expectedCalls) {
    if (array_values($expectedCalls) !== $expectedCalls) {
      throw new \InvalidArgumentException('$expectedCalls must be a numeric array with no keys missing.');
    }
    for ($i = 0; TRUE; ++$i) {
      $actualCall = isset($this->calls[$i]) ? $this->calls[$i] : NULL;
      $expectedCall = isset($expectedCalls[$i]) ? $expectedCalls[$i] : NULL;
      if (NULL === $actualCall && NULL === $expectedCall) {
        break;
      }
      if (NULL === $actualCall) {
        $testCase->fail("Call $i missing.\nExpected: " . var_export($expectedCall, TRUE));
        break;
      }
      if (NULL === $expectedCall) {
        $testCase->fail("Call $i was not expected.\nActual: " . var_export($actualCall, TRUE));
        break;
      }
      if ($actualCall !== $expectedCall) {
        $testCase->fail("Call $i mismatch.\nExpected: " . var_export($expectedCall, TRUE) . "\nActual: " . var_export($this->calls[$i], TRUE));
        break;
      }
    }
    $testCase->assertEquals($expectedCalls, $this->calls);
  }
} 
