<?php

namespace Drupal\xautoload\ClassLoader;

use Drupal\xautoload\CacheManager\CacheManagerObserverInterface;
use Drupal\xautoload\ClassFinder\ClassFinderInterface;
use Drupal\xautoload\ClassFinder\InjectedApi\LoadClassGetFileInjectedApi;
use Drupal\xautoload\DataCache\DataCacheInterface;

/**
 * Bass class for cached class loader decorators where cache entries cannot be
 * written one by one, but have to be written all at once instead.
 *
 * Saving the cache immediately on every cache miss would be too expensive. On
 * the other hand, saving only at the end of the request might fail if the
 * request does not end properly, or if some classes are still loaded after the
 * end-of-process callback.
 *
 * The solution is an exponentially growing queue. Cache writing happens not on
 * every cache miss, but only on the 1st, 3rd, 7th, 15th, 31st, 63rd etc.
 *
 * This will result in a "hot" cache after a limited number of requests, and
 * with a limited number of cache write operations.
 */
class ClassLoader_Cache_IncrementalClassmap
  extends AbstractClassLoaderDecorator
  implements CacheManagerObserverInterface {

  /**
   * @var \Drupal\xautoload\DataCache\DataCacheInterface
   */
  private $dataCache;

  /**
   * @var int
   */
  private $nMax = 1;

  /**
   * @var int
   */
  private $n = 0;

  /**
   * @var string[]
   */
  private $toBeDeleted = array();

  /**
   * @var string[]
   */
  private $toBeAdded = array();

  /**
   * @var string[]
   */
  private $classFiles;

  /**
   * @var string
   */
  private $signature;

  /**
   * @param \Drupal\xautoload\ClassFinder\ClassFinderInterface $finder
   * @param \Drupal\xautoload\DataCache\DataCacheInterface $dataCache
   * @param string $dynamicKey
   */
  public function __construct(ClassFinderInterface $finder, DataCacheInterface $dataCache, $dynamicKey) {
    parent::__construct($finder);
    $this->dataCache = $dataCache;
    $this->setCachePrefix($dynamicKey);
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    if (isset($this->classFiles[$class])) {
      $file = $this->classFiles[$class];
      // The is_file() check may cost around 0.0045 ms per class file, but this
      // depends on your system of course.
      if (is_file($file)) {
        require $file;

        return;
      }
      $this->toBeDeleted[$class] = $file;
      unset($this->classFiles[$class]);
      ++$this->n;
    }

    // Resolve cache miss.
    $api = new LoadClassGetFileInjectedApi($class);
    if ($this->finder->apiFindFile($api, $class)) {
      // Queue the result for the cache.
      $this->toBeAdded[$class]
        = $this->classFiles[$class]
        = $api->getFile();
      ++$this->n;
    }

    // Save the cache if enough has been queued up.
    if ($this->n >= $this->nMax) {
      $this->classFiles = $this->updateClassFiles($this->toBeAdded, $this->toBeDeleted);
      $this->toBeDeleted = array();
      $this->toBeAdded = array();
      $this->nMax *= 2;
      $this->n = 0;
    }
  }

  /**
   * Set the new cache prefix after a flush cache.
   *
   * @param string $prefix
   *   A prefix for the storage key in APC.
   */
  function setCachePrefix($prefix) {

    if (0
      || FALSE === ($data = $this->dataCache->load())
      || !isset($data['signature'])
      || $prefix !== $data['signature']
    ) {
      $this->dataCache->save([
        'signature' => $prefix,
        'classmap' => [],
      ]);
      $this->classFiles = [];
    }
    elseif (0
      || empty($data['classmap'])
      || !is_array($data['classmap'])
    ) {
      $this->classFiles = [];
    }
    else {
      $this->classFiles = $data['classmap'];
    }

    $this->signature = $prefix;
  }

  /**
   * @param string[] $toBeAdded
   * @param string[] $toBeRemoved
   *
   * @return string[]
   */
  private function updateClassFiles($toBeAdded, $toBeRemoved) {

    $class_files = $toBeAdded;
    // Other requests may have already written to the cache, so we get an up to
    // date version.


    if (0
      || FALSE === ($data = $this->dataCache->load())
      || !isset($data['signature'])
      || $this->signature !== $data['signature']
    ) {
      // Give up writing in this request.
      return $class_files;
    }
    elseif (0
      || empty($data['classmap'])
      || !is_array($data['classmap'])
    ) {
      // Leave $class_files as they are.
    }
    else {
      $class_files += $data['classmap'];
      foreach ($toBeRemoved as $class => $file) {
        if (isset($class_files[$class]) && $class_files[$class] === $file) {
          unset($class_files[$class]);
        }
      }
    }

    $this->dataCache->save([
      'signature' => $this->signature,
      'classmap' => $class_files,
    ]);

    return $class_files;
  }

}
