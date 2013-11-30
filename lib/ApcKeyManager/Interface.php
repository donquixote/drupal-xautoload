<?php

interface xautoload_ApcKeyManager_Interface {

  /**
   * @param xautoload_LoaderFactory|xautoload_LoaderManager $observer
   */
  function observeApcPrefix($observer);

  /**
   * Get a fresh APC prefix.
   */
  function renewApcPrefix();
}
