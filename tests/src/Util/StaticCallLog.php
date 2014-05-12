<?php


namespace Drupal\xautoload\Tests\Util;


abstract class StaticCallLog {

  /**
   * @var CallLog|null
   */
  private static $callLog;

  /**
   * @param CallLog $callLog
   *
   * @throws \Exception
   */
  static function setCallLog(CallLog $callLog) {
    if (isset(self::$callLog)) {
      throw new \Exception("StaticCallLog already initialized.");
    }
    self::$callLog = $callLog;
  }

  /**
   * Uninitialize.
   *
   * @throws \Exception
   */
  static function unsetCallLog() {
    if (!isset(self::$callLog)) {
      throw new \Exception("StaticCallLog not initialized yet.");
    }
    self::$callLog = NULL;
  }

  /**
   * called from stream wrapper code in
   * @see ExampleModules::setupExampleModuleFiles()
   *
   * @throws \Exception
   */
  static function addCall() {
    if (!isset(self::$callLog)) {
      throw new \Exception("StaticCallLog not initialized yet.");
    }
    $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $call = $trace[1];
    $callFiltered = array();
    foreach (array('function', 'class', 'type') as $key) {
      if (isset($call[$key])) {
        $callFiltered[$key] = $call[$key];
      }
    }
    $callFiltered['args'] = array();
    foreach ($call['args'] as $arg) {
      if (is_array($arg)) {
        $arg = '(array)';
      }
      elseif (is_object($arg)) {
        $arg = '(' . get_class($arg) . ')';
      }
      $callFiltered['args'][] = $arg;
    }
    self::$callLog->addCall($callFiltered);
  }
} 
