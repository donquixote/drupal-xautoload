<?php

/**
 * A number of static methods that don't interact with any global state.
 */
class xautoload_Util {

  /**
   * Generate a random string made of uppercase and lowercase characters and numbers.
   *
   * @param int $length
   *   Length of the random string to generate
   *
   * @return string
   *   Random string of the specified length
   */
  static function randomString($length = 30) {

    // $chars - allowed characters
    $chars = 'abcdefghijklmnopqrstuvwxyz' .
             'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
             '1234567890';

    srand((double)microtime() * 1000000);

    $pass = '';
    for ($i = 0; $i < $length; ++$i) {
      $num = rand() % strlen($chars);
      $tmp = substr($chars, $num, 1);
      $pass .= $tmp;
    }

    return $pass;
  }
}

