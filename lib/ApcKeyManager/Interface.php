<?php

interface xautoload_ApcKeyManager_Interface {

  /**
   * @param xautoload_ApcKeyManager_ObserverInterface $observer
   */
  function observeApcPrefix($observer);

  /**
   * Generate a fresh APC prefix, and replace the old one.
   */
  function renewApcPrefix();
}
