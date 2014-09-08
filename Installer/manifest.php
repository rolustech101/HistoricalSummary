<?php

$manifest = array (
  'acceptable_sugar_versions' => 
  array (
    'exact_matches' => 
    array (
      0 => '7.2.2.0',
      1 => '7.5.0.0',
      2 => '7.5.0.0beta1',
      3 => '7.5.0.0beta2',
      4 => '7.5.0.0beta3',
      5 => '7.5.0.0beta4',
      6 => '7.5.0.0RC1',
      7 => '7.5.0.0RC2',
      8 => '7.6.0.0',
    ),
  ),
  'acceptable_sugar_flavors' => 
  array (
    0 => 'PRO',
    1 => 'CORP',
    2 => 'ENT',
    3 => 'ULT',
  ),
  'readme' => 'README.txt',
  'key' => 1410195817,
  'author' => 'jclark',
  'description' => 'Installs a new historical summary view similar to the 6.x version of activity history popup.',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'Historical Summary',
  'published_date' => '2014-09-08 17:03:37',
  'type' => 'module',
  'version' => '1.0',
  'remove_tables' => '',
);

$installdefs = array (
  'id' => 1410195817,
  'copy' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/copy/custom/clients/base/api/CustomHistorySummaryAPI.php',
      'to' => 'custom/clients/base/api/CustomHistorySummaryAPI.php',
    ),
    1 => 
    array (
      'from' => '<basepath>/copy/custom/clients/base/layouts/history-summary/history-summary.php',
      'to' => 'custom/clients/base/layouts/history-summary/history-summary.php',
    ),
    2 => 
    array (
      'from' => '<basepath>/copy/custom/clients/base/views/custom-history-summary/',
      'to' => 'custom/clients/base/views/custom-history-summary/',
    ),
  ),
  'post_execute' => 
  array (
    0 => '<basepath>/post_execute/0.php',
  ),
  'post_uninstall' => 
  array (
    0 => '<basepath>/post_uninstall/0.php',
  ),
);

?>
