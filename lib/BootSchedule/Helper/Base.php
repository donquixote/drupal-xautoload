<?php

abstract class xautoload_BootSchedule_Helper_Base implements xautoload_BootSchedule_Helper_Interface {

  /**
   * @var xautoload_ClassFinder_Interface|xautoload_ClassFinder_Prefix|xautoload_ClassFinder_NamespaceOrPrefix
   */
  protected $finder;

  /**
   * @param xautoload_ClassFinder_Interface $finder
   *   The class finder where we register the namespaces and prefixes.
   */
  function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * @param xautoload_ClassFinder_Interface $finder
   *   The class finder where we register the namespaces and prefixes.
   * @throws Exception
   */
  function verifyFinderInstance($finder) {
    if ($finder !== $this->finder) {
      throw new Exception("Wrong finder instance.");
    }
  }

  /**
   * Invoke hook_xautoload or another registration hook.
   *
   * @param string $hook
   *   A string to identify a hook, e.g. 'xautoload' for hook_xautoload().
   */
  function invokeRegistrationHook($hook) {
    // Let other modules register stuff to the finder via hook_xautoload().
    $api = new xautoload_InjectedAPI_hookXautoload($this->finder);
    foreach (module_implements($hook) as $module) {
      $api->setModule($module);
      $f = $module . '_' . $hook;
      $f($api);
    }
  }

  /**
   * Register prefix maps, one map per extension type.
   *
   * @param array[] $prefix_maps
   *   Prefix maps for different extension types. Modules and themes are
   *   registered speparately, because they need a different MissingDirPlugin.
   */
  protected function registerPrefixMaps($prefix_maps) {
    /**
     * @var string $type
     *   E.g. 'module' or 'theme'.
     * @var string[] $map
     *   E.g. $map['system'] = 'modules/system/lib/Drupal/system'
     */
    foreach ($prefix_maps as $type => $map) {
      $missing_dir_plugin = new xautoload_MissingDirPlugin_DrupalExtensionPrefix($type, TRUE);
      $this->finder->registerPrefixesDeep($map, $missing_dir_plugin);
    }
  }

  /**
   * Register namespace maps, one map per extension type.
   *
   * @param array[] $namespace_maps
   *   Namespace maps for different extension types. Modules and themes are
   *   registered speparately, because they need a different MissingDirPlugin.
   */
  protected function registerNamespaceMaps($namespace_maps) {
    /**
     * @var string $type
     *   E.g. 'module' or 'theme'.
     * @var string[] $map
     *   E.g. $map['Drupal\system'] = 'modules/system/lib/Drupal/system'
     */
    foreach ($namespace_maps as $type => $map) {
      $missing_dir_plugin = new xautoload_MissingDirPlugin_DrupalExtensionNamespace($type, FALSE);
      $this->finder->registerNamespacesDeep($map, $missing_dir_plugin);
    }
  }

  /**
   * Register namespace maps for PSR-4, one map per extension type.
   *
   * @param array[] $psr4_maps
   *   Namespace maps for different extension types. Modules and themes are
   *   registered speparately, because they need a different MissingDirPlugin.
   */
  protected function registerPsr4Maps($psr4_maps) {
    /**
     * @var string $type
     *   E.g. 'module' or 'theme'.
     * @var string[] $map
     *   E.g. $map['Drupal\system'] = 'modules/system/lib/Drupal/system'
     */
    foreach ($psr4_maps as $type => $map) {
      $plugin = new xautoload_FinderPlugin_Psr4();
      $this->finder->registerNamespacesPlugin($map, $plugin);
    }
  }
}
