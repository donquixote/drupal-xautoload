<?php


interface xautoload_MissingDirPlugin_Interface {

  /**
   * @param string $path_fragment
   * @return string
   */
  function alternativeDir($path_fragment);
} 