<?php

/**
 * Service that knows how to register module namespaces and prefixes into the
 * class loader, and that remembers which modules have already been registered.
 */
class xautoload_Adapter_DrupalExtensionAdapter {

  /**
   * @var xautoload_DrupalSystem_Interface
   */
  protected $system;

  /**
   * @var xautoload_ClassFinder_ExtendedInterface
   */
  protected $finder;

  /**
   * @var xautoload_FinderPlugin_Interface[]
   */
  protected $namespaceBehaviors = array();

  /**
   * @var xautoload_FinderPlugin_Interface[]
   */
  protected $prefixBehaviors = array();

  /**
   * @var xautoload_ClassFinder_Helper_Map
   */
  protected $namespaceMap;

  /**
   * @var xautoload_ClassFinder_Helper_Map
   */
  protected $prefixMap;

  /**
   * @var bool[]
   *   Which modules have already been processed.
   */
  protected $registered = array();

  /**
   * @var xautoload_DirectoryBehavior_Default
   */
  protected $defaultBehavior;

  /**
   * @param xautoload_DrupalSystem_Interface $system
   * @param xautoload_ClassFinder_ExtendedInterface $finder
   */
  function __construct($system, $finder) {
    $this->system = $system;
    $this->finder = $finder;
    $this->namespaceMap = $finder->getNamespaceMap();
    $this->prefixMap = $finder->getPrefixMap();
    foreach (array('module', 'theme') as $extension_type) {
      $this->namespaceBehaviors[$extension_type] = new xautoload_FinderPlugin_DrupalExtensionNamespace(
        $extension_type,
        $this->namespaceMap,
        $this->prefixMap,
        $this->system)
      ;
      $this->prefixBehaviors[$extension_type] = new xautoload_FinderPlugin_DrupalExtensionUnderscore(
        $extension_type,
        $this->namespaceMap,
        $this->prefixMap,
        $this->system)
      ;
    }
    $this->defaultBehavior = new xautoload_DirectoryBehavior_Default();
  }

  /**
   * Register lazy plugins for enabled Drupal modules and themes, assuming that
   * we don't know yet whether they use PSR-0, PEAR-Flat, or none of these.
   *
   * @param string[] $extension_names
   *   Extension names.
   */
  function registerExtensionsByName($extension_names) {
    $this->registerExtensions($this->system->getExtensionTypes($extension_names));
  }

  function registerActiveExtensions() {
    $this->registerExtensions($this->system->getActiveExtensions());
  }

  /**
   * Register lazy plugins for enabled Drupal modules and themes, assuming that
   * we don't know yet whether they use PSR-0, PEAR-Flat, or none of these.
   *
   * @param string[] $extensions
   *   An array where the keys are extension names, and the values are extension
   *   types like 'module' or 'theme'.
   */
  function registerExtensions(array $extensions) {

    $prefix_map = array();
    $namespace_map = array();
    foreach ($extensions as $name => $type) {
      if (!empty($this->registered[$name])) {
        // The extension has already been processed.
        continue;
      }
      $namespace_map['Drupal/' . $name . '/'][$name] = $this->namespaceBehaviors[$type];
      $prefix_map[str_replace('_', '/', $name) . '/'][$name] = $this->prefixBehaviors[$type];
      $this->registered[$name] = TRUE;
    }
    $this->namespaceMap->registerDeepPaths($namespace_map);
    $this->prefixMap->registerDeepPaths($prefix_map);
  }

  /**
   * Register lazy plugins for a given extension, assuming that we don't know
   * yet whether it uses PSR-0, PEAR-Flat, or none of these.
   *
   * @param string $name
   * @param string $type
   */
  function registerExtension($name, $type) {
    if (!empty($this->registered[$name])) {
      // The extension has already been processed.
      return;
    }
    $this->namespaceMap->registerDeepPath('Drupal/' . $name . '/', $name, $this->namespaceBehaviors[$type]);
    $this->prefixMap->registerDeepPath(str_replace('_', '/', $name) . '/', $name, $this->prefixBehaviors[$type]);
    $this->registered[$name] = TRUE;
  }

  /**
   * Register PSR-4 directory for an extension.
   * Override previous settings for this extension.
   *
   * @param string $name
   *   The extension name.
   * @param string $extension_dir
   *   The directory of the extension.
   * @param string $subdir
   *   The PSR-4 base directory, relative to the extension directory.
   *   E.g. 'lib' or 'src'.
   */
  function registerExtensionPsr4($name, $extension_dir, $subdir) {
    if (!empty($this->registered[$name])) {
      if ('psr-4' === $this->registered[$name]) {
        // It already happened.
        return;
      }
      // Unregister the lazy plugins.
      $this->namespaceMap->unregisterDeepPath('Drupal/' . $name . '/', $name);
      $this->prefixMap->unregisterDeepPath(str_replace('_', '/', $name) . '/', $name);
    }
    $dir = strlen($subdir)
      ? $extension_dir . '/' . trim($subdir, '/') . '/'
      : $extension_dir . '/'
    ;
    $this->namespaceMap->registerDeepPath('Drupal/' . $name . '/', $dir, $this->defaultBehavior);
    // Re-add the PSR-0 test directory, for consistency's sake.
    if (is_dir($psr0_tests_dir = $extension_dir . '/lib/Drupal/' . $name . '/Tests')) {
      $this->namespaceMap->registerDeepPath('Drupal/' . $name . '/Tests/', $psr0_tests_dir, $this->defaultBehavior);
    }
  }
}
