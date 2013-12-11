<?php

class xautoload_Discovery_ComposerJsonTargetDir extends xautoload_Discovery_ComposerJson {

  /**
   * @var string
   */
  protected $targetDir;

  /**
   * @param array $data
   * @param string $path_prefix
   */
  protected function __construct(array $data, $path_prefix) {
    parent::__construct($data, $path_prefix);
    $this->targetDir = rtrim($data['target-dir'], '/') . '/';
  }

  /**
   * @param xautoload_Adapter_ClassFinderAdapter $adapter
   *
   * @throws Exception
   */
  function writeToAdapter($adapter) {

    if (!empty($data['include-path'])) {
      $paths = $this->pathsResolveTargetDir((array)$data['include-path']);
      $this->addIncludePaths($paths, $this->pathPrefix);
    }

    if (!empty($data['autoload']['psr-0'])) {
      $this->addMultipleWithTargetDir($adapter, $data['autoload']['psr-0']);
    }

    if (!empty($data['autoload']['psr-4'])) {
      throw new Exception("PSR-4 is incompatible with target-dir.");
    }

    if (!empty($data['autoload']['classmap'])) {
      $paths = $this->pathsResolveTargetDir($data['autoload']['classmap']);
      $this->addClassmapSources($adapter, $paths);
    }

    if (!empty($data['autoload']['files'])) {
      $paths = $this->pathsResolveTargetDir($data['autoload']['files']);
      foreach ($paths as $file) {
        require $this->pathPrefix . $file;
      }
    }
  }

  /**
   * @param string[] $paths
   *
   * @return string[]
   */
  protected function pathsResolveTargetDir(array $paths) {
    $strlen = strlen($this->targetDir);
    foreach ($paths as &$path) {
      if (0 === strpos($path, $this->targetDir)) {
        $path = substr($path, $strlen);
      }
    }
    return $paths;
  }

  /**
   * @param xautoload_Adapter_ClassFinderAdapter $adapter
   * @param array $prefixes
   */
  protected function addMultipleWithTargetDir($adapter, array $prefixes) {
    $default_behavior = new xautoload_DirectoryBehavior_Default();
    $psr0_behavior = new xautoload_DirectoryBehavior_Psr0();
    $namespace_map = array();
    $prefix_map = array();
    $target_dir_strlen = strlen($this->targetDir);
    foreach ($prefixes as $prefix => $paths) {
      if (FALSE === strpos($prefix, '\\')) {
        $logical_base_path = xautoload_Util::prefixLogicalPath($prefix);
        foreach ((array)$paths as $root_path) {
          $deep_path = strlen($root_path) ? (rtrim($root_path, '/') . '/' . $logical_base_path) : $logical_base_path;
          if (0 !== strpos($deep_path, $this->targetDir))  {
            continue;
          }
          $deep_path = $this->pathPrefix . substr($deep_path, $target_dir_strlen);
          $prefix_map[$logical_base_path][$deep_path] = $default_behavior;
        }
      }
      $logical_base_path = xautoload_Util::namespaceLogicalPath($prefix);
      foreach ((array)$paths as $root_path) {
        $deep_path = strlen($root_path) ? (rtrim($root_path, '/') . '/' . $logical_base_path) : $logical_base_path;
        if (0 !== strpos($deep_path, $this->targetDir))  {
          continue;
        }
        $deep_path = $this->pathPrefix . substr($deep_path, $target_dir_strlen);
        $namespace_map[$logical_base_path][$deep_path] = $psr0_behavior;
      }
    }
    if (!empty($prefix_map)) {
      $adapter->getPrefixMap()->registerDeepPaths($prefix_map);
    }
    $adapter->getNamespaceMap()->registerDeepPaths($namespace_map);
  }
}