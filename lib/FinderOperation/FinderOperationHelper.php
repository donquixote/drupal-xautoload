<?php

namespace Drupal\xautoload\FinderOperation;

use Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface;
use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;
use stdClass;
use Drupal\xautoload\ClassFinder\Plugin\DrupalExtensionNamespaceFinderPlugin;
use Drupal\xautoload\ClassFinder\Plugin\DrupalExtensionUnderscoreFinderPlugin;

class FinderOperationHelper {

  /**
   * @var DrupalSystemInterface
   */
  protected $system;

  /**
   * @param DrupalSystemInterface $system
   */
  function __construct($system) {
    $this->system = $system;
  }

  /**
   * Register prefixes and namespaces for enabled Drupal modules and themes.
   *
   * @param ExtendedClassFinderInterface $finder
   * @param stdClass[] $extensions
   *   Info about extensions.
   */
  function registerExtensions($finder, $extensions) {

    // Prepare the behaviors.
    $namespace_behaviors = array();
    $prefix_behaviors = array();
    foreach (array('module', 'theme') as $extension_type) {
      $namespace_behaviors[$extension_type] = new DrupalExtensionNamespaceFinderPlugin($extension_type
        , $finder->getNamespaceMap()
        , $finder->getPrefixMap()
        , $this->system
      );
      $prefix_behaviors[$extension_type] = new DrupalExtensionUnderscoreFinderPlugin($extension_type
        , $finder->getNamespaceMap()
        , $finder->getPrefixMap()
        , $this->system
      );
    }

    $prefix_map = array();
    if (version_compare(PHP_VERSION, '5.3') >= 0) {
      // PHP 5.3+ mode
      $namespace_map = array();
      foreach ($extensions as $info) {
        $namespace_map['Drupal/' . $info->name . '/'][$info->name] = $namespace_behaviors[$info->type];
        $prefix_map[str_replace(
          '_',
          '/',
          $info->name
        ) . '/'][$info->name] = $prefix_behaviors[$info->type];
      }
      $finder->getNamespaceMap()->registerDeepPaths($namespace_map);
    }
    else {
      // PHP 5.2 mode
      foreach ($extensions as $info) {
        $prefix_map[str_replace(
          '_',
          '/',
          $info->name
        ) . '/'][$info->name] = $namespace_behaviors[$info->type];
      }
    }
    $finder->getPrefixMap()->registerDeepPaths($prefix_map);
  }
}
