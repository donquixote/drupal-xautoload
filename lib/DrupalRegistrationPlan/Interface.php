<?php

interface xautoload_DrupalRegistrationPlan_Interface {

  /**
   * Init the phase where all boostrap modules are known,
   * and register the respective namespaces and prefixes.
   */
  function start();

  /**
   * Init the phase where all modules are known,
   * and register the respective namespaces and prefixes.
   */
  function mainPhase();

  /**
   * Add modules after they have been enabled or installed.
   *
   * @param array $modules
   *   Array of module names, with numeric keys.
   */
  function addModules(array $modules);
}
