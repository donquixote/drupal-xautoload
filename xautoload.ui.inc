<?php

/**
 * Implements hook_form_FORM_ID_alter()
 * with FORM_ID = "system_performance_settings"
 *
 * @param array $form
 */
function xautoload_form_system_performance_settings_alter(&$form) {

  $form['xautoload'] = array(
    /* @see system_element_info() */
    '#type' => 'fieldset',
    '#title' => t('X Autoload'),
  );

  $cache_default_value = variable_get('xautoload_cache_types', array());

  $cache_status = array(
    'apc' => (extension_loaded('apc') && function_exists('apc_store')),
    'apcu' => (extension_loaded('apcu') && function_exists('apcu_store')),
    'wincache' => (extension_loaded('WinCache') && function_exists('wincache_ucache_get')),
    'xcache' => (extension_loaded('Xcache') && function_exists('xcache_get')),
    'dbcache' => TRUE,
  );
  $cache_names = array(
    'apc' => 'APC',
    'apcu' => 'APCu',
    'wincache' => 'WinCache',
    'xcache' => 'XCache',
    'dbcache' => 'Smart database cache (experimental)',
  );
  $options = $cache_names;
  $options_descriptions = array();
  $options['dbcache'] = l($options['dbcache'], 'https://www.drupal.org/node/2451261');
  $active_cache_key = NULL;
  $active_cache_name = t('No cache.');
  foreach ($cache_names as $key => $title) {
    $status = $cache_status[$key];
    if (!isset($active_cache_key) && $status && !empty($cache_default_value[$key])) {
      $active_cache_key = $key;
      $active_cache_name = $title;
    }
  }
  foreach ($options as $key => $title) {
    if ($cache_status[$key]) {
      $options[$key] .= ' (' . t('Running and available') . ')';
    }
    else {
      $options[$key] .= ' (' . t('Not currently available') . ')';
      $options[$key] = '<em>' . $options[$key] . '</em>';
    }
  }

  $form['xautoload']['xautoload_cache_types'] = array(
    /* @see system_element_info() */
    '#type' => 'checkboxes',
    '#title' => t('Cache mode'),
    '#default_value' => $cache_default_value,
    '#options' => $options,
    '#options_descriptions' => $options_descriptions,
    '#description' => ''
      . '<p>' . t('X Autoload will pick the first cache mode that is <em>available and enabled</em>.')
      . '<br/>' . t('Currently, this is:') . ' ' . $active_cache_name . '.'
      . '</p><p>' . t('It is usually safe to enable all these checkboxes, so xautoload can always use the best cache mode available on your system.')
      . '<br/>' . t('This also makes it easier to sync these settings between environments, where different PHP extensions might be installed.')
      . '</p>',
  );

  $form['xautoload']['xautoload_cache_lazy'] = array(
    '#type' => 'checkbox',
    '#title' => t('Postpone registration of module namespaces until the first cache miss (recommended).'),
    '#default_value' => variable_get('xautoload_cache_lazy', FALSE),
    '#description' => t('This should speed up the bootstrap of xautoload.'),
  );

  $form['xautoload']['xautoload_replace_core'] = array(
    '#type' => 'checkbox',
    '#title' => t('Replace core class loader.'),
    '#default_value' => variable_get('xautoload_replace_core', FALSE),
    '#description' => t('Lets xautoload replace Drupal\'s drupal_autoload_class() and drupal_autoload_interface().')
      . '<br/>' . t('Improves performance, if at least one of the cache options is active and enabled.')
      . '<br/>' . t('This features is quite new. Please report any problems in the xautoload issue queue on drupal.org.'),
  );
}
