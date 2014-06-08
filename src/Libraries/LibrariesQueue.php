<?php


namespace Drupal\xautoload\Libraries;


use Drupal\xautoload\CacheMissObserver\CacheMissObserverInterface;
use Drupal\xautoload\ClassFinder\ExtendedClassFinderInterface;

class LibrariesQueue implements CacheMissObserverInterface {

  /**
   * @var bool
   *   TRUE, if the class finder is no longer the cached one..
   */
  private $awake = FALSE;

  /**
   * @var ExtendedClassFinderInterface
   */
  private $finder;

  /**
   * @var array[]
   */
  private $queue = array();

  /**
   * Executes the operation.
   *
   * This method will only be called if and when the "real" class finder is
   * initialized.
   *
   * @param ExtendedClassFinderInterface $finder
   *   The class finder.
   */
  function cacheMiss($finder) {
    # \krumong()->devel->dpm(__METHOD__);
    $this->awake = TRUE;
    $this->finder = $finder;
    foreach ($this->queue as $library) {
      $this->loadLibrary($library);
    }
    // For consistency, queue is always empty after cache miss.
    $this->queue = array();
  }

  /**
   * Queues up a library, so its autoload stuff gets added on first cache miss.
   *
   * @param array $library
   *
   * @see LibrariesPreLoadCallback::__invoke()
   */
  function addLibraryToQueue($library) {
    \krumong()->devel->dpm($library, __METHOD__);
    if ($this->awake) {
      // Cache miss already happened, do it directly.
      $this->loadLibrary($library);
    }
    else {
      $this->queue[] = $library;
    }
  }

  /**
   * @param array $library
   */
  private function loadLibrary($library) {
    if (0
      || !isset($library['xautoload'])
      || !isset($library['library path'])
    ) {
      \krumong()->devel->dpm($library, __METHOD__);
      return;
    }

    if (0
      || !is_callable($library['xautoload'])
    ) {
      \krumong()->devel->dpm($library, __METHOD__);
      return;
    }

    \krumong()->devel->dpm($library, __METHOD__);

    $adapter = \xautoload_InjectedAPI_hookXautoload::create(
      $this->finder,
      $library['library path']);

    call_user_func(
      $library['xautoload'],
      $adapter,
      $library['library path']);
  }
}
