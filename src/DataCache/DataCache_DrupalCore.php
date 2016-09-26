<?php

namespace Drupal\xautoload\DataCache;

class DataCache_DrupalCore implements DataCacheInterface {

  /**
   * @var string
   */
  private $key;

  /**
   * @param string $key
   */
  public function __construct($key) {
    $this->key = $key;
  }

  /**
   * @return array|false
   */
  public function load() {
    return ($cached = cache_get($this->key))
      ? $cached->data
      : FALSE;
  }

  /**
   * @param array $data
   */
  public function save(array $data) {
    cache_set($this->key, $data);
  }
}
