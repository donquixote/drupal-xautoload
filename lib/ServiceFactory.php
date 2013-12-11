<?php


class xautoload_ServiceFactory {

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_Main
   */
  function main($services) {
    return new xautoload_Main($services);
  }

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_Adapter_ClassFinderAdapter
   */
  function adapter($services) {
    return new xautoload_Adapter_ClassFinderAdapter($services->finder, $services->classMapGenerator);
  }

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_Discovery_ClassMapGenerator
   */
  function classMapGenerator($services) {
    return new xautoload_Discovery_CachedClassMapGenerator($services->classMapGeneratorRaw);
  }

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_Discovery_ClassMapGenerator
   */
  function classMapGeneratorRaw($services) {
    return new xautoload_Discovery_ClassMapGenerator();
  }

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_Adapter_DrupalExtensionAdapter
   */
  function extensionRegistrationService($services) {
    return new xautoload_Adapter_DrupalExtensionAdapter($services->system, $services->finder);
  }

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_CacheManager
   */
  function cacheManager($services) {
    return xautoload_CacheManager::create();
  }

  /**
   * Proxy class finder.
   *
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_ClassFinder_Interface
   *   Proxy object wrapping the class finder.
   *   This is used to delay namespace registration until the first time the
   *   finder is actually used.
   *   (which might never happen thanks to the APC cache)
   */
  function proxyFinder($services) {
    // The class finder is cheap to create, so it can use an identity proxy.
    return new xautoload_ClassFinder_Proxy($services->finder, $services->extensionRegistrationService);
  }

  /**
   * The class finder (alias for 'finder').
   *
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_ClassFinder_Interface
   *   Object that can find classes,
   *   and provides methods to register namespaces and prefixes.
   *   Note: The findClass() method expects an InjectedAPI argument.
   */
  function classFinder($services) {
    return $services->finder;
  }

  /**
   * The class finder (alias for 'classFinder').
   *
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_ClassFinder_Interface
   *   Object that can find classes,
   *   and provides methods to register namespaces and prefixes.
   *   Notes:
   *   - The findClass() method expects an InjectedAPI argument.
   *   - namespaces are only supported since PHP 5.3
   */
  function finder($services) {
    return new xautoload_ClassFinder_NamespaceOrPrefix();
  }

  /**
   * @param xautoload_Container_LazyServices $services
   *
   * @return xautoload_DrupalSystem_Interface
   */
  function system($services) {
    return new xautoload_DrupalSystem_Real();
  }
}

