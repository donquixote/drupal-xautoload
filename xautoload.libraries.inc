<?php
use Drupal\xautoload\Libraries\SerializableClosureWrapper;
use Drupal\xautoload\Libraries\LibrariesPreLoadCallback;

/**
 * Implements hook_libraries_info_alter()
 *
 * Replaces xautoload-related closures in libraries_info(), so the info can be
 * serialized. Allows recovery of the closures on unserialize().
 *
 * @param array[] $info
 */
function xautoload_libraries_info_alter(&$info) {

  foreach ($info as $library_name => &$library_info) {
    if (0
      || !isset($library_info['xautoload'])
      || !is_callable($library_info['xautoload'])
    ) {
      continue;
    }
    $callable = $library_info['xautoload'];
    if ($callable instanceof Closure) {
      // Wrap the closure so it can be serialized.
      $callable = new SerializableClosureWrapper(
        $library_info['xautoload'],
        // The module name and library name allow the closure to be recovered on
        // unserialize.
        $library_info['module'],
        $library_name);
    }
    unset($library_info['xautoload']);
    $library_info['callbacks']['pre-load'][] = new LibrariesPreLoadCallback($callable);
  }
}
