<?php


/**
 * X Autoload plugins are for:
 *   - More exotic autoload patterns that are incompatible with PSR-0 or PEAR
 *   - Situations where we don't want to register a ton of namespaces, and using
 *     a plugin instead gives us performance benefits.
 */
abstract class xautoload_FinderPlugin_WithKillswitch implements xautoload_FinderPlugin_Interface {

  /**
   * @var xautoload_ClassFinder_Helper_Map
   */
  protected $map;

  /**
   * @var string
   */
  protected $key;

  /**
   * @var int
   */
  protected $id;

  /**
   * Allow the namespace plugin to unsubscribe or replace itself.
   * This is called by the ClassFinder itself, the moment the namespace plugin
   * is registered.
   *
   * @param xautoload_ClassFinder_Helper_Map $map
   *   Object where the thing is subscribed.
   * @param string $key
   * @param int $id
   */
  function setKillswitch($map, $key, $id) {
    $this->map = $map;
    $this->key = $key;
    $this->id = $id;
  }
}
