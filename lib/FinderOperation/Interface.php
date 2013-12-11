<?php

interface xautoload_FinderOperation_Interface {

  /**
   * @param xautoload_ClassFinder_ExtendedInterface $finder
   * @param xautoload_Adapter_DrupalExtensionAdapter $helper
   */
  function operateOnFinder($finder, $helper);
}