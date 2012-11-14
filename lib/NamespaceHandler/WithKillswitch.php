<?php


/**
 * Namespace handlers are for:
 *   - More exotic autoload patterns that are incompatible with PSR-0 or PEAR
 *   - Situations where we don't want to register a ton of namespaces, and using
 *     a handler instead gives us performance benefits.
 */
abstract class xautoload_NamespaceHandler_WithKillswitch implements xautoload_NamespaceHandler_Interface {

  protected $map;
  protected $key;
  protected $id;

  /**
   * Allow the namespace handler to unsubscribe or replace itself.
   * This is called by the ClassFinder itself, the moment the namespace handler
   * is registered.
   *
   * @param xautoload_ClassFinder_Helper_Map $map
   *   Object where the thing is subscribed.
   */
  function setKillswitch($map, $key, $id) {
    $this->map = $map;
    $this->key = $key;
    $this->id = $id;
  }
}
