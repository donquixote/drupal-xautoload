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

  /**
   * Returns the argument.
   * This can be useful to pass around as a callback.
   *
   * @param mixed $arg
   *   The argument.
   *
   * @return mixed
   *   The argument, returned.
   */
  static function identity($arg) {
    return $arg;
  }

  /**
   * @param mixed $arg
   *   A value to be returned by the callback.
   * @return callback
   *   A callback with no arguments, that will always return the value specified
   *   in $arg.
   */
  static function identityCallback($arg) {
    return array(new xautoload_Container_Identity($arg), 'get');
  }

  /**
   * @param object $container
   *   An object with a __get() method.
   * @param string $key
   *   Key to be passed to the __get() method as an argument.
   *
   * @return callback
   *   Callback with no arguments, that will return the result of
   *   $container->__get($key).
   */
  static function containerCallback($container, $key) {
    return array(new xautoload_Container_MagicGet($container, $key), 'get');
  }

  /**
   * Get a string representation of a callback for debug purposes.
   *
   * @param callback $callback
   *   A PHP callback, which could be an array or a string.
   *
   * @return string
   *   A string representation to be displayed to a user, e.g.
   *   "Foo::staticMethod()", or "Foo->bar()"
   */
  static function callbackToString($callback) {
    if (is_array($callback)) {
      list($obj, $method) = $callback;
      if (is_object($obj)) {
        $str = get_class($obj) . '->' . $method . '()';
      }
      else {
        $str = $obj . '::';
        $str .= $method . '()';
      }
    }
    else {
      $str = $callback;
    }
    return $str;
  }
}

