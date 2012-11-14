<?php


class xautoload_DrupalExtensionSystem {

  protected $paths = array();
  protected $inMainPhase = FALSE;

  function getExtensionPath($name) {
    if (isset($this->paths[$name])) {
      return $this->paths[$name];
    }
    // Now let's check if we're still in bootstrap phase, and should switch to
    // main phase.
    if (!$this->inMainPhase) {
      if (module_exists($name)) {
        // The module_list() knows more than we do.
        // Time to switch to main phase.
        $this->mainPhase();
        return isset($this->paths[$name]) ? $this->paths[$name] : NULL;
      }
    }
  }

  /**
   * This is called to initiate the bootstrap phase.
   */
  function bootstrapPhase() {
    // Register all bootstrap modules.
    // On bootstrap, the values of system_list() equal the keys.
    foreach (system_list('bootstrap') as $extension) {
      // drupal_get_filename() is used during bootstrap, so that should be safe.
      $filename = drupal_get_filename('module', $extension);
      $this->paths[$extension] = dirname($filename);
    }
  }

  /**
   * This is called at the beginning of hook_init().
   */
  function mainPhase() {
    foreach (system_list('module_enabled') as $extension => $info) {
      $this->paths[$extension] = dirname($info->filename);
    }
    foreach (list_themes('theme') as $extension => $info) {
      $this->paths[$extension] = dirname($info->filename);
    }
    $this->inMainPhase = TRUE;
  }
}
