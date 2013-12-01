<?php

require_once __DIR__ . '/../xautoload.early.inc';

xautoload(NULL)->finder->registerNamespaceRoot('Drupal\xautoload\Tests', __DIR__);
xautoload(NULL)->finder->registerNamespaceRoot('Drupal\xautoload\Tests', dirname(__DIR__) . '/lib');