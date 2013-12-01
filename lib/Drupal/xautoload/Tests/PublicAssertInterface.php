<?php
/**
 * Author: lemonhead
 */

namespace Drupal\xautoload\Tests;


interface PublicAssertInterface {

  /**
   * @param bool $status
   * @param string $message
   *
   * @return bool
   */
  function assertPublic($status, $message);
} 