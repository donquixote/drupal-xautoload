<?php


namespace Drupal\xautoload;


use Drupal\xautoload\DrupalSystem\DrupalSystemInterface;

class LibrariesIntegration {

  /**
   * @var DrupalSystemInterface
   */
  private $system;

  /**
   * @param DrupalSystemInterface $system
   */
  function __construct(DrupalSystemInterface $system) {
    $this->system = $system;
  }

  /**
   * Called from libraries_xautoload()
   *
   * @param \xautoload_InjectedAPI_hookXautoload $adapter
   *   An adapter object that can register stuff into the class loader.
   */
  public function init($adapter) {
    foreach ($this->system->getLibrariesInfo() as $name => $info) {
      if (isset($info['xautoload']) && is_callable($f = $info['xautoload'])) {
        $adapter->setExtensionDir($dir = $this->system->librariesGetPath($name));
        call_user_func($f, $adapter, $dir);
      }
    }
  }

} 
