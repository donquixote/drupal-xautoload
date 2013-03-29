<?php

class xautoload_DrupalRegistrationPlan_Lazy {

  protected $delayed = array();
  protected $plan;
  protected $services;

  function __construct($plan, $services) {
    $this->plan = $plan;
    $this->services = $services;
  }

  /**
   * Register prefixes and namespaces for enabled Drupal extensions.
   * (for namespaces, look at xautoload_DrupalRegistrationPlan_PHP53)
   */
  function start() {
    $this->delayed[] = 'start';
  }

  /**
   * This is called during hook_init() / hook_custom_theme().
   */
  function mainPhase() {
    $this->delayed[] = 'mainPhase';
  }

  /**
   * Add modules after they have been enabled or installed.
   *
   * @param array $modules
   *   Array of module names, with numeric keys.
   */
  function addModules(array $modules) {
    $this->flush();
    $this->plan->addModules($modules);
  }

  function flush() {
    $t0 = microtime(TRUE);
    foreach ($this->delayed as $method) {
      $this->plan->$method();
      $t1 = microtime(TRUE);
      var_dump("$method: " . (1000 * ($t1 - $t0)));
      $t0 = $t1;
    }
    $this->services->set('plan', $this->plan);
  }
}
