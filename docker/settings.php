<?php

// @codingStandardsIgnoreFile

/**
 * The UN-OCHA section.
 *
 * Please forget all that has come before.
 *
 * Configure the database for the Drupal via environment variables.
 *
 * Configure everything else via config snippets in a mounted volume on the
 * path /srv/www/shared/settings. This means that this settings.php file can
 * be the same for all properties.
 *
 * The volume should be replaced (eventually) with a secrets store of some sort.
 *
 * Yay!
 */

// Populate the database settings with the environment variables if defined.
$databases['default']['default'] = array_filter([
  'database'  => getenv('DRUPAL_DB_DATABASE'),
  'username'  => getenv('DRUPAL_DB_USERNAME'),
  'password'  => getenv('DRUPAL_DB_PASSWORD'),
  'host'      => getenv('DRUPAL_DB_HOST'),
  'port'      => getenv('DRUPAL_DB_PORT'),
  'driver'    => getenv('DRUPAL_DB_DRIVER'),
  'prefix'    => getenv('DRUPAL_DB_PREFIX'),
  'charset'   => getenv('DRUPAL_DB_CHARSET'),
  'collation' => getenv('DRUPAL_DB_COLLATION'),
  'sql_mode'  => "SET sql_mode = 'ANSI,STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER'",
]);
// Load everything else from snippets under /srv/www/shared/settings.
// @TODO: Use some sort of key/value store.
if (file_exists('/srv/www/shared/settings')) {
  foreach (glob('/srv/www/shared/settings/settings.*.php') as $filename) {
    include $filename;
  }
}
