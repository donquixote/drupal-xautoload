<?php

class xautoload_ApcKeyManager_Enabled implements xautoload_ApcKeyManager_Interface {

  /**
   * @var string
   */
  protected $apcKey;

  /**
   * @var string
   */
  protected $apcPrefix;

  /**
   * @var array
   */
  protected $observers = array();

  /**
   * @param string $apc_key
   */
  function __construct($apc_key) {

    $this->apcKey = $apc_key;
    $this->apcPrefix = apc_fetch($this->apcKey);

    if (empty($this->apcPrefix)) {
      $this->renewApcPrefix();
    }
  }

  /**
   * @param xautoload_LoaderFactory|xautoload_LoaderManager $observer
   */
  function observeApcPrefix($observer) {
    $observer->setApcPrefix($this->apcPrefix);
    $this->observers[] = $observer;
  }

  /**
   * Get a fresh APC prefix.
   */
  function renewApcPrefix() {

    // Generate a new APC prefix
    $this->apcPrefix = xautoload_Util::randomString();

    // Store the APC prefix
    apc_store($this->apcKey, $this->apcPrefix);

    /**
     * @var xautoload_LoaderFactory|xautoload_LoaderManager $observer
     */
    foreach ($this->observers as $observer) {
      $observer->setApcPrefix($this->apcPrefix);
    }
  }
}
