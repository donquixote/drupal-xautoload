<?php


class xautoload_Discovery_CachedClassMapGenerator implements xautoload_Discovery_ClassMapGeneratorInterface {

  /**
   * @var xautoload_Discovery_ClassMapGeneratorInterface
   */
  protected $decorated;

  /**
   * @param xautoload_Discovery_ClassMapGeneratorInterface $decorated
   */
  function __construct($decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @param string[] $paths
   *
   * @return string[]
   */
  function wildcardPathsToClassmap($paths) {
    // Attempt to load from cache.
    $cid = 'xautoload:wildcardPathsToClassmap:' . md5(serialize($paths));
    $cache = cache_get($cid);
    if ($cache && isset($cache->data)) {
      return $cache->data;
    }
    // Resolve cache miss and save.
    $map = $this->decorated->wildcardPathsToClassmap($paths);
    cache_set($cid, $map);
    return $map;
  }
} 