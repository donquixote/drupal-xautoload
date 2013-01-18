<?php


class xautoload_Plugin_DrupalExtensionLibPrefixDeep extends xautoload_Plugin_WithKillswitch {

  protected $system;

  /**
   * @param xautoload_DrupalExtensionSystem $system
   *   Abstraction of Drupal's extension system.
   */
  function __construct($system) {
    $this->system = $system;
  }

  /**
   * The way this plugin is registered, it will only ever be called if the
   * class is not within a namespace. This means, all the DIRECTORY_SEPARATOR in
   * the $path parameter were underscores before.
   *
   * @param xautoload_InjectedAPI_findFile $api
   *   API object with suggestFile() method.
   * @param string $empty_string
   *   The way we register this plugin, this parameter will always be empty
   *   string.
   * @param string $path
   *   The class name converted into a path by usual PEAR rules. All underscores
   *   of the class name are replaced by DIRECTORY_SEPARATOR
   *
   * @return boolean
   *   TRUE, if we found it.
   */
  function findFile($api, $empty_string, $path) {

    // Find the first occurance of "/X", where X can be any uppercase letter.
    // We could do that with regex, but that's too expensive.
    $pos = 0;
    while (TRUE) {
      $pos = strpos($path, DIRECTORY_SEPARATOR);
      if (FALSE === $pos) {
        // There is no place like DIRECTORY_SEPARATOR + uppercase character in
        // the path. Thus, this is not a class we are interested in.
        return FALSE;
      }
      // Pick the character following the DIRECTORY_SEPARATOR.
      $char = @$path[$pos + 1];
      if (isset($char) && 'A' <= $char && $char <= 'Z') {
        // We found a '_' followed by an uppercase character.
        break;
      }
      // We hit a normal DIRECTORY_SEPARATOR followed by a lowercase character.
      // We assume this previously was a '_' in the module name.
      $path[$pos] = '_';
    }

    if (FALSE !== $pos) {
      // We found a position, so let's work with that.
      $extension = substr($path, 0, $pos);
      $extension_path = $this->system->getExtensionPath($extension);
      if (!empty($extension_path)) {
        $path = $extension_path . '/lib/' . substr($path, $pos + 1);
        if ($api->suggestFile($path)) {
          return TRUE;
        }
      }
    }
  }
}
