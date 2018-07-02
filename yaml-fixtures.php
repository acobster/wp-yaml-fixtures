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

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/danielbachhuber/dictator/dictator.php';

WP_CLI::add_command('fixture', new FixtureCommand());
