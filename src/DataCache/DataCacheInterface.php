<?php

namespace Drupal\xautoload\DataCache;

interface DataCacheInterface {

  /**
   * @return array|false
   */
  public function load();

  /**
   * @param array $data
   */
  public function save(array $data);

}
