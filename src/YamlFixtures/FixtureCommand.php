<?php

namespace YamlFixtures;

use Symfony\Component\Yaml\Yaml;
use WP_CLI;
use WP_CLI_Command;

use YamlFixtures\Fixture\FixtureFactory;
use YamlFixtures\Fixture\BlankSlateFixture;
use YamlFixtures\Fixture\OptionsFixture;
use YamlFixtures\Fixture\UsersFixture;
use YamlFixtures\Fixture\TaxonomiesFixture;
use YamlFixtures\Fixture\PostsFixture;

class FixtureCommand extends WP_CLI_Command {
  const FIXTURE_ORDER = [
    BlankSlateFixture::class,
    OptionsFixture::class,
    UsersFixture::class,
    TaxonomiesFixture::class,
    PostsFixture::class,
  ];

  /**
   * Installs a fixture.
   *
   * ## OPTIONS
   *
   * <file>
   * : The path of the fixture file to parse and install.
   *
   * [--blank-slate]
   * : Whether to clear out posts, terms, and users from the database.
   * ---
   * Default: false
   * ---
   *
   * ## EXAMPLES
   *
   *   wp fixture install example.yaml
   *
   * @when after_wp_load
   */
  function install( $args, $assoc_args ) {
    $file = $args[0];

    try {
      $definitions = Yaml::parseFile($file);
    } catch (ParseException $e) {
      WP_CLI::error(sprintf(
        'Error parsing %s: %s',
        $file,
        $e->getMessage()
      ));
    }

    // TODO override --blank-slate

    foreach ($this->compileFixtures($definitions) as $fixture) {
      // TODO try/catch, --force option
      $fixture->install();
    }
  }

  protected function compileFixtures(array $definitions) {
    $fixtures = array_map(
      [FixtureFactory::class, 'create'],
      array_keys($definitions),
      array_values($definitions)
    );

    usort($fixtures, function($f1, $f2) {
      $idx1 = array_search(get_class($f1), static::FIXTURE_ORDER, true);
      $idx2 = array_search(get_class($f2), static::FIXTURE_ORDER, true);

      return $idx1 - $idx2;
    });

    return $fixtures;
  }
}
