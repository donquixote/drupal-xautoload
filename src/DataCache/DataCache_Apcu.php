<?php

namespace Drupal\xautoload\DataCache;

class DataCache_Apcu implements DataCacheInterface {

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
    return apcu_fetch($this->key);
  }

  /**
   * @param array $data
   */
  public function save(array $data) {
    apcu_store($this->key);
  }
}
