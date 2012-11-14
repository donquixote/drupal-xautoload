<?php


class xautoload_NamespaceHandler_DrupalExtensionLibPSR0 extends xautoload_NamespaceHandler_WithKillswitch {

  protected $system;

  /**
   * @param xautoload_DrupalExtensionSystem $system
   *   Abstraction of Drupal's extension system.
   */
  function __construct($system) {
    $this->system = $system;
  }

  /**
   * The way this handler is registered, it will only ever be called if the
   * class is within the \Drupal\ namespace. All the DIRECTORY_SEPARATOR in the
   * $path parameter were namespace separators before.
   *
   * @param xautoload_InjectedAPI_findFile $api
   *   API object with suggestFile() method.
   * @param string $Drupal_string
   *   The way we register this handler, this parameter will always be a string
   *   with value "Drupal/".
   * @param string $path
   *   The part of the PSR-0 path after "Drupal/". E.g., if the class is
   *   "Drupal\ab_cd\SomeClass", then this will be "ab_cd/SomeClass.php".
   *
   * @return boolean
   *   TRUE, if we found it.
   */
  function findFile($api, $Drupal_string, $path) {

    $pos = strpos($path, DIRECTORY_SEPARATOR);

    if (FALSE !== $pos) {
      $extension = substr($path, 0, $pos);
      $extension_path = $this->system->getExtensionPath($extension);
      if (!empty($extension_path)) {
        $path = $extension_path . '/lib/Drupal/' . $path;
        if ($api->suggestFile($path)) {
          return TRUE;
        }
      }
    }
  }
}
