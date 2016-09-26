<?php

namespace Drupal\xautoload\ClassLoader;

use Drupal\xautoload\CacheManager\CacheManagerObserverInterface;
use Drupal\xautoload\ClassFinder\ClassFinderInterface;
use Drupal\xautoload\ClassFinder\InjectedApi\LoadClassGetFileInjectedApi;

class XCacheClassLoader extends AbstractClassLoaderDecorator implements CacheManagerObserverInterface {

  /**
   * @var string
   */
  private $uniqueSiteHash;

  /**
   * @var string
   */
  private $prefix;

  /**
   * @param \Drupal\xautoload\ClassFinder\ClassFinderInterface $finder
   * @param string $uniqueSiteHash
   * @param string $dynamicKey
   */
  public function __construct(ClassFinderInterface $finder, $uniqueSiteHash, $dynamicKey) {
    if (!$this->checkRequirements()) {
      throw new \RuntimeException("Extension 'xcache' is missing.");
    }
    parent::__construct($finder);
    $this->uniqueSiteHash = $uniqueSiteHash;
    $this->setCachePrefix($dynamicKey);
  }

  /**
   * @return bool
   */
  protected function checkRequirements() {
    return extension_loaded('Xcache') && function_exists('xcache_isset') && defined('XC_TYPE_PHP');
  }

  /**
   * {@inheritdoc}
   */
  function loadClass($class) {

    // Look if the cache has anything for this class.
    // @todo Use a suffix instead of a prefix? For faster lookup?
    // See http://stackoverflow.com/questions/39701930/is-apcu-fetch-lookup-faster-with-prefix-or-suffix
    if (xcache_isset($this->prefix . $class)
      && $file = xcache_get($this->prefix . $class)
    ) {
      if (is_file($file)) {
        require $file;

        return;
      }
      xcache_unset($this->prefix . $class);
    }

    // Resolve cache miss.
    $api = new LoadClassGetFileInjectedApi($class);
    if ($this->finder->apiFindFile($api, $class)) {
      xcache_set($this->prefix . $class, $api->getFile());
    }
  }

  /**
   * @param string $dynamicKey
   */
  public function setCachePrefix($dynamicKey) {

    $signature_key = 'xautoload-key-value-signature-' . $this->uniqueSiteHash;

    if (FALSE === $signature = apcu_fetch($signature_key)) {
      // Signature missing.
      apcu_store($signature_key, $dynamicKey);
    }
    elseif ($signature !== $dynamicKey) {
      // Signature mismatch.
      /** @noinspection PhpUndefinedConstantInspection */
      xcache_clear_cache(XC_TYPE_PHP);
      xcache_set($signature_key, $dynamicKey);
    }

    $this->prefix = md5($this->uniqueSiteHash . '|' . $dynamicKey);
  }
}
