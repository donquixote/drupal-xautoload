<?php


/**
 * That's an abstraction of the Drupal module/theme system.
 * It can tell us when an extension exists, and at which path.
 */
class xautoload_DrupalExtensionSystem {

  protected $paths = array();
  protected $inMainPhase = FALSE;

  /**
   * Get the directory path for a module or theme.
   *
   * @param $name
   *   Name of the module or theme.
   */
  function getExtensionPath($name) {
    if (isset($this->paths[$name])) {
      return $this->paths[$name];
    }
    // Now let's check if we're still in bootstrap phase, and should switch to
    // main phase.
    // TODO: That's expensive, isn't it?
    // TODO: What about themes? What if we want a class in a theme, before
    //   a module class switches this to main phase?
    if (!$this->inMainPhase) {
      // We want an indicator to tell us when we are done with bootstrap.
      // module_exists() seems like a good candidate. If we call that with an
      // existing non-bootstrap mdoule and it returns true, we know we are past
      // bootstrap.
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
    foreach (system_list('bootstrap') as $module) {
      // drupal_get_filename() is used during bootstrap, so that should be safe.
      $filename = drupal_get_filename('module', $module);
      $this->paths[$module] = dirname($filename);
    }
  }

  /**
   * This is called to initiate the main phase.
   */
  function mainPhase() {
    foreach (system_list('module_enabled') as $module => $info) {
      $this->paths[$module] = dirname($info->filename);
    }
    foreach (list_themes('theme') as $theme => $info) {
      $this->paths[$theme] = dirname($info->filename);
    }
    $this->inMainPhase = TRUE;
  }

  /**
   * Add modules after they have been enabled or installed.
   *
   * @param array $modules
   *   Array of module names, with numeric keys.
   */
  function addModules(array $modules) {
    foreach ($modules as $module) {
      $filename = drupal_get_filename('module', $module);
      $this->paths[$module] = dirname($filename);
    }
  }
}
