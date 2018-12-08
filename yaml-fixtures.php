<?php

/**
 * Plugin Name: WP YAML Fixtures
 * Description: Define WordPress database snapshots as simple YAML files,
 * for scaffolding and testing your WordPress tools
 */

// only take effect on the command line
if ( ! defined('WP_CLI') || ! WP_CLI ) {
  return;
}

use YamlFixtures\FixtureCommand;

// check for autoload file in well-known places
foreach ([
  __DIR__ . '/vendor/autoload.php',
  __DIR__ . '/../../autoload.php',
] as $autoload) {
  if (file_exists($autoload)) {
    require_once $autoload;
    break;
  }
}

WP_CLI::add_command('fixture', new FixtureCommand());
