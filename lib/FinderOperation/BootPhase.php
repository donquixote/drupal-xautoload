<?php

class xautoload_FinderOperation_BootPhase implements xautoload_FinderOperation_Interface {

  /**
   * {@inheritdoc}
   */
  function operateOnFinder($finder, $helper) {
    $helper->registerActiveExtensions();
  }
}