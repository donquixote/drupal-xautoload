<?php

class xautoload_ClassFinder_Proxy implements xautoload_ClassFinder_Interface {

  protected $loader;
  protected $finder;
  protected $plan;

  function __construct($loader, $finder, $registration_plan) {
    $this->loader = $loader;
    $this->finder = $finder;
    $this->plan = $registration_plan;
  }

  function findFile($api, $class) {
    print __CLASS__ . "::findFile(\$api, '$class')\n";
    $this->loader->setFinder($this->finder);
    $this->plan->flush();
    return $this->finder->findFile($api, $class);
  }
}
