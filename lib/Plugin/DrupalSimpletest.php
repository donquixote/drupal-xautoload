<?php


class xautoload_Plugin_DrupalSimpletest extends xautoload_Plugin_WithKillswitch {

  protected $extensionsRaw;
  protected $extensions = array();

  /**
   * Expect a class Drupal\(module)\Tests\SomeTest
   * to be in (module dir)/lib/Drupal/(module)/Tests/SomeTest.php,
   * but consider the PHP include_path setting.
   *
   * @param object $api
   *   The InjectedAPI object.
   * @param string $Drupal_string
   *   The way this plugin is registered, this will be "Drupal/".
   * @param string $second_part
   *   Second part of the path, e.g. "$module/Tests/SomeTest.php".
   */
  function findFile($api, $Drupal_string, $second_part) {
    $pos = strpos($second_part, DIRECTORY_SEPARATOR);
    if (
      $pos !== FALSE &&
      substr($second_part, $pos + 1, 6) === 'Tests' . DIRECTORY_SEPARATOR
    ) {
      $extension = substr($second_part, 0, $pos);
      $extension_lib_drupal_dir = $this->getExtensionPath($extension);
      if (!empty($extension_lib_drupal_dir)) {
        $path = $extension_lib_drupal_dir . $second_part;
        if ($api->suggestFile($path)) {
          return TRUE;
        }
      }
    }
  }

  /**
   * Helper function to get the path for an extension.
   */
  protected function getExtensionPath($extension) {

    // Ok, this is a lot of nested if().
    // But trust me, that's the most performant way to cut it.

    // Do we already have this extension's path in cache?
    if (!isset($this->extensions[$extension])) {

      // .. Nope.
      // But we might have the path to the .module or .info file, and only need
      // to get the dirname() of that.
      if (!isset($this->extensionsRaw[$extension])) {

        // Ok we don't even have that. Are we even initialized?
        if (isset($this->extensionsRaw)) {

          // Yes, we are already initialized.
          // So apparently the extension doesn't exist.
          return;
        }
        else {
          // Initialize the extensionsRaw.
          // The filename can be e.g.
          //   (module path)/(module name).module,  OR
          //   (theme path)/(theme name).info
          $this->extensionsRaw = db_query("SELECT name, filename FROM {system}")->fetchAllKeyed();

          // We could now continue to process all of those to get the dirname(),
          // but we prefer it the lazy way.

          // Now it could be that we suddenly have our extension.
          if (!isset($this->extensionsRaw[$extension])) {
            // Nope, still don't have it.
            return;
          }
        }
      }
      // Ok, now we know the extension is in $this->extensionsRaw, but not in
      // $this->extensions.
      $this->extensions[$extension] = dirname($this->extensionsRaw[$extension]) . '/lib/Drupal/';
    }

    // Now we know that we have the ../lib/Drupal/ path for this extension.
    return $this->extensions[$extension];
  }
}
