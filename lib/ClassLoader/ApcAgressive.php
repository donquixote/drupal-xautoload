<?php


class xautoload_ClassLoader_ApcAgressive extends xautoload_ClassLoader_ApcCache {

  /**
   * For compatibility, it is possible to use the class loader as a finder.
   *
   * @param string $class
   *   The class to find.
   *
   * @return string
   *   File where the class is assumed to be.
   */
  function findFile($class) {

    if (FALSE === $file = apc_fetch($this->prefix . $class)) {
      apc_store($this->prefix . $class, $file = parent::findFile($class));
    }

    return $file;
  }
}
