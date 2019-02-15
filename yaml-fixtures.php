<?php

/**
 * Plugin Name: WP YAML Fixtures
 * Description: Define WordPress database snapshots as simple YAML files,
 * for scaffolding and testing your WordPress tools
 */

use YamlFixtures\FixtureCommand;

$autoloadPaths = [
  __DIR__ . '/vendor/autoload.php',
  __DIR__ . '/../../autoload.php',
];

if (
  defined('ABSPATH')
  && !in_array(ABSPATH . '/vendor/autoload.php', $autoloadPaths)
) {
  $autoloadPaths[] = ABSPATH . '/vendor/autoload.php';
}

// check for autoload file in well-known places
foreach ($autoloadPaths as $autoload) {
  if (file_exists($autoload)) {
    require_once $autoload;
    break;
  }
}


// WP_CLI declarations should only take effect on the command line
if ( ! defined('WP_CLI') || ! WP_CLI ) {
  return;
}


WP_CLI::add_command('fixture', new FixtureCommand());
