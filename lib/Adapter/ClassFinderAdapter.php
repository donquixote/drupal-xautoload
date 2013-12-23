<?php


namespace Drupal\xautoload\Adapter;

use Drupal\xautoload\Util;
use Drupal\xautoload\DirectoryBehavior\DefaultDirectoryBehavior;
use Drupal\xautoload\Discovery\ComposerDir;
use Drupal\xautoload\Discovery\ComposerJson;
use Drupal\xautoload\ClassFinder\GenericPrefixMap;
use Drupal\xautoload\DirectoryBehavior\Psr0DirectoryBehavior;
use Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface;
use Drupal\xautoload\Discovery\ClassMapGeneratorInterface;

/**
 * An instance of this class is passed around to implementations of
 * hook_xautoload(). It acts as a wrapper around the ClassFinder, to register
 * stuff.
 */
class ClassFinderAdapter {

  /**
   * @var ExtendedClassFinderInterface
   */
  protected $finder;

  /**
   * @var GenericPrefixMap
   */
  protected $prefixMap;

  /**
   * @var GenericPrefixMap
   */
  protected $namespaceMap;

  /**
   * @var ClassMapGeneratorInterface
   */
  protected $classMapGenerator;

  /**
   * @param ExtendedClassFinderInterface $finder
   *   The class finder object.
   * @param ClassMapGeneratorInterface $classmap_generator
   */
  function __construct($finder, $classmap_generator) {
    $this->finder = $finder;
    $this->prefixMap = $finder->getPrefixMap();
    $this->namespaceMap = $finder->getNamespaceMap();
    $this->defaultBehavior = new DefaultDirectoryBehavior();
    $this->psr0Behavior = new Psr0DirectoryBehavior();
    $this->classMapGenerator = $classmap_generator;
  }

  /**
   * @return \Drupal\xautoload\ClassFinder\GenericPrefixMap
   */
  function getNamespaceMap() {
    return $this->namespaceMap;
  }

  /**
   * @return GenericPrefixMap
   */
  function getPrefixMap() {
    return $this->prefixMap;
  }

  /**
   * @return ClassMapGeneratorInterface
   */
  function getClassmapGenerator() {
    return $this->classMapGenerator;
  }

  //                                                                   Discovery
  // ---------------------------------------------------------------------------

  /**
   * @param string[] $paths
   *   File paths or wildcard paths for class discovery.
   */
  function addClassmapSources($paths) {
    $map = $this->classMapGenerator->wildcardPathsToClassmap($paths);
    $this->addClassMap($map);
  }

  //                                                              Composer tools
  // ---------------------------------------------------------------------------

  /**
   * Scan a composer.json file provided by a Composer package.
   *
   * @param string $file
   *
   * @throws \Exception
   */
  function composerJson($file) {
    $json = ComposerJson::createFromFile($file);
    $json->writeToAdapter($this);
  }

  /**
   * Scan a directory containing Composer-generated autoload files.
   *
   * @param string $dir
   *   Directory to look for Composer-generated files. Typically this is the
   *   ../vendor/composer dir.
   */
  function composerDir($dir) {
    $dir = ComposerDir::create($dir);
    $dir->writeToAdapter($this);
  }

  //                                                      multiple PSR-0 / PSR-4
  // ---------------------------------------------------------------------------

  /**
   * Add multiple PSR-0 namespaces
   *
   * @param array $prefixes
   */
  function addMultiple(array $prefixes) {
    $namespace_map = array();
    $prefix_map = array();
    foreach ($prefixes as $prefix => $paths) {
      if (FALSE === strpos($prefix, '\\')) {
        $logical_base_path = Util::prefixLogicalPath($prefix);
        foreach ((array) $paths as $root_path) {
          $deep_path = strlen($root_path) ? (rtrim(
              $root_path,
              '/'
            ) . '/' . $logical_base_path) : $logical_base_path;
          $prefix_map[$logical_base_path][$deep_path] = $this->defaultBehavior;
        }
      }
      $logical_base_path = Util::namespaceLogicalPath($prefix);
      foreach ((array) $paths as $root_path) {
        $deep_path = strlen($root_path) ? (rtrim(
            $root_path,
            '/'
          ) . '/' . $logical_base_path) : $logical_base_path;
        $namespace_map[$logical_base_path][$deep_path] = $this->psr0Behavior;
      }
    }
    if (!empty($prefix_map)) {
      $this->prefixMap->registerDeepPaths($prefix_map);
    }
    $this->namespaceMap->registerDeepPaths($namespace_map);
  }

  /**
   * Add multiple PSR-4 namespaces
   *
   * @param array $map
   */
  function addMultiplePsr4(array $map) {
    $namespace_map = array();
    foreach ($map as $namespace => $paths) {
      $logical_base_path = Util::namespaceLogicalPath($namespace);
      foreach ($paths as $root_path) {
        $deep_path = strlen($root_path) ? (rtrim($root_path, '/') . '/') : '';
        $namespace_map[$logical_base_path][$deep_path] = $this->defaultBehavior;
      }
    }
    $this->namespaceMap->registerDeepPaths($namespace_map);
  }

  //                                                        Composer ClassLoader
  // ---------------------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  function addClassMap(array $classMap) {
    $this->finder->registerClasses($classMap);
  }

  /**
   * Add PSR-0 style prefixes.
   *
   * @param string $prefix
   * @param string[]|string $paths
   */
  function add($prefix, $paths) {
    if (FALSE === strpos($prefix, '\\')) {
      // Due to the ambiguity of PSR-0, this could be either PEAR-like or namespaced.
      $logical_base_path = Util::prefixLogicalPath($prefix);
      foreach ((array) $paths as $root_path) {
        $deep_path = strlen($root_path) ? (rtrim(
            $root_path,
            '/'
          ) . '/' . $logical_base_path) : $logical_base_path;
        $this->prefixMap->registerDeepPath(
          $logical_base_path,
          $deep_path,
          $this->defaultBehavior
        );
      }
    }
    // Namespaced PSR-0
    $logical_base_path = Util::namespaceLogicalPath($prefix);
    foreach ((array) $paths as $root_path) {
      $deep_path = strlen($root_path) ? (rtrim(
          $root_path,
          '/'
        ) . '/' . $logical_base_path) : $logical_base_path;
      $this->namespaceMap->registerDeepPath(
        $logical_base_path,
        $deep_path,
        $this->psr0Behavior
      );
    }
  }

  /**
   * @param string $prefix
   * @param string[]|string $paths
   */
  function addPsr4($prefix, $paths) {
    // Namespaced PSR-4
    $logical_base_path = Util::namespaceLogicalPath($prefix);
    foreach ((array) $paths as $deep_path) {
      $deep_path = strlen($deep_path) ? (rtrim($deep_path, '/') . '/') : '';
      $this->namespaceMap->registerDeepPath(
        $logical_base_path,
        $deep_path,
        $this->defaultBehavior
      );
    }
  }

  //                                                      More convenience stuff
  // ---------------------------------------------------------------------------

  /**
   * Add PSR-0 style namespace.
   * This will assume that we are really dealing with a namespace, even if it
   * has no '\\' included.
   *
   * @param string $prefix
   * @param string[]|string $paths
   */
  function addNamespacePsr0($prefix, $paths) {
    $logical_base_path = Util::namespaceLogicalPath($prefix);
    foreach ((array) $paths as $root_path) {
      $deep_path = strlen($root_path) ? (rtrim(
          $root_path,
          '/'
        ) . '/' . $logical_base_path) : $logical_base_path;
      $this->namespaceMap->registerDeepPath(
        $logical_base_path,
        $deep_path,
        $this->psr0Behavior
      );
    }
  }

  /**
   * Add PEAR-like prefix.
   * This will assume with no further checks that $prefix contains no namespace
   * separator.
   *
   * @param $prefix
   * @param $paths
   */
  function addPear($prefix, $paths) {
    $logical_base_path = Util::prefixLogicalPath($prefix);
    foreach ((array) $paths as $root_path) {
      $deep_path = strlen($root_path) ? (rtrim(
          $root_path,
          '/'
        ) . '/' . $logical_base_path) : $logical_base_path;
      $this->prefixMap->registerDeepPath(
        $logical_base_path,
        $deep_path,
        $this->defaultBehavior
      );
    }
  }
}
