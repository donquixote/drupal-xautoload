<?php

class xautoload_ApcKeyManager_Disabled implements xautoload_ApcKeyManager_Interface {

  /**
   * {@inheritdoc}
   */
  function observeApcPrefix($observer) {}

  /**
   * {@inheritdoc}
   */
  function renewApcPrefix() {}
}
