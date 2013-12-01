<?php


namespace Drupal\xautoload\Tests;


class DummyFilesystem {

  /**
   * @var array
   */
  protected $knownPaths = array();

  const NOTHING = FALSE;
  const DIR = '(dir)';
  const FILE = '(file)';

  /**
   * @param string $file
   * @param string $class
   * @throws \Exception
   */
  function addClass($file, $class) {
    $this->addKnownFile($file);
    if (self::FILE !== ($existing = $this->knownPaths[$file])) {
      throw new \Exception("A class '$existing' already exists at '$file'. Cannot overwrite with class '$class'.");
    }
    $this->knownPaths[$file] = $class;
  }

  /**
   * @param array $files
   */
  function addKnownFiles($files) {
    foreach ($files as $file) {
      $this->addKnownFile($file);
    }
  }

  /**
   * @param string $file
   *
   * @throws \Exception
   */
  function addKnownFile($file) {
    if (!isset($this->knownPaths[$file])) {
      $this->knownPaths[$file] = self::FILE;
      $this->addKnownDir(dirname($file));
    }
    elseif (self::DIR === $this->knownPaths[$file]) {
      throw new \Exception("A directory already exists at '$file', cannot overwrite with a file.");
    }
  }

  /**
   * @param string $dir
   */
  function addKnownDir($dir) {
    if (FALSE === strpos($dir, '://')) {
      return;
    }
    if (!isset($this->knownPaths[$dir])) {
      // Need to set parents first.
      $this->addKnownDir(dirname($dir));
    }
    $this->knownPaths[$dir] = self::DIR;
  }

  function resolvePath($path) {
    if (isset($this->knownPaths[$path])) {
      return $this->knownPaths[$path];
    }
    else {
      return self::NOTHING;
    }
  }

  /**
   * @param string $path
   *
   * @return array
   */
  function getStat($path) {
    if (!isset($this->knownPaths[$path])) {
      // File does not exist.
      return FALSE;
    }
    elseif (self::DIR === $this->knownPaths[$path]) {
      return stat(__DIR__);
    }
    else {
      // Create a tmp file with the contents and get its stats.
      $contents = $this->getFileContents($path);
      $resource = tmpfile();
      fwrite($resource, $contents);
      $stat = fstat($resource);
      fclose($resource);
      return $stat;
    }
  }

  /**
   * @param $path
   *   The file path.
   *
   * @return string
   *   The file contents.
   *
   * @throws \Exception
   *   Exception thrown if there is no file at $path.
   */
  function getFileContents($path) {
    if (!isset($this->knownPaths[$path])) {
      // File does not exist.
      throw new \Exception("Assumed file '$path' does not exist.");
    }
    elseif (self::DIR === $this->knownPaths[$path]) {
      throw new \Exception("Assumed file '$path' is a directory.");
    }

    if (self::FILE === $this->knownPaths[$path]) {
      // Empty PHP file..
      return '<?php';
    }

    // PHP file with class definition.
    $class = $this->knownPaths[$path];

    if (FALSE === ($pos = strrpos($class, '\\'))) {
      // Class without namespace.
      return <<<EOT
<?php
class $class {}
EOT;
    }

    // Class without namespace.
    $namespace = substr($class, 0, $pos);
    $classname = substr($class, $pos + 1);
    return <<<EOT
<?php
namespace $namespace;
class $classname {}
EOT;
  }
} 