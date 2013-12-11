<?php


interface xautoload_Discovery_ClassMapGeneratorInterface {

  /**
   * @param string[] $paths
   *
   * @return string[]
   */
  function wildcardPathsToClassmap($paths);
} 