<?php


namespace Drupal\xautoload;


class BootPhaseController {

  /**
   * Called when all bootstrap module files have been included, so
   * hook_xautoload() can be called on bootstrap modules.
   */
  function initBootstrapVariables() {

  }

  /**
   * Called when all module files have been included, so hook_xautoload() can
   * be called on all modules.
   */
  function initBootstrapFull() {

  }
} 
