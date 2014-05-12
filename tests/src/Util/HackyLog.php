<?php


namespace Drupal\xautoload\Tests\Util;


class HackyLog {

  /**
   * @throws \Exception
   */
  static function log() {
    $args = func_get_args();
    throw new \Exception(var_export($args, TRUE));
  }
} 
