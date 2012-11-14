<?php

namespace Drupal\xautoload_example\Tests;

class ExampleWebTest extends \DrupalWebTestCase {

  static function getInfo() {
    return array(
      'name' => 'X Autoload example web test',
      'description' => 'This test class is only to prove that disabled modules still have their web tests working.',
      'group' => 'X Autoload Example',
    );
  }

  function testStringConcat() {
    // TODO: We could something really web-testy here..
    $this->assert('aa' + 'bb' == 'aabb', "'aa' + 'bb' == 'aabb'");
  }
}
