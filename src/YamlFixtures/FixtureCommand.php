<?php

/**
 * YamlFixtures\FixtureCommand class
 */

namespace YamlFixtures;

use Symfony\Component\Yaml\Yaml;
use WP_CLI;
use WP_CLI_Command;

use YamlFixtures\Fixture\FixtureFactory;
use YamlFixtures\Fixture\BlankSlateFixture;
use YamlFixtures\Fixture\HomePageSlugFixture;
use YamlFixtures\Fixture\OptionsFixture;
use YamlFixtures\Fixture\UsersFixture;
use YamlFixtures\Fixture\TaxonomiesFixture;
use YamlFixtures\Fixture\PostsFixture;

/**
 * WP-CLI command for installing YAML fixtures
 */
class FixtureCommand extends WP_CLI_Command {
  const FIXTURE_ORDER = [
    BlankSlateFixture::class,
    OptionsFixture::class,
    UsersFixture::class,
    TaxonomiesFixture::class,
    PostsFixture::class,
    HomePageSlugFixture::class,
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
   * [--yes]
   * : Confirm all prompts.
   *
   * ## EXAMPLES
   *
   *   wp fixture install example.yaml
   *
   * @when after_wp_load
   */
  public function install( $args, $options ) {
    $file = $args[0];

    WP_CLI::confirm( 'This may destroy your database content. Are you sure?', $options );

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

  /**
   * Create and sort Fixture instances from a YAML-defined array
   *
   * @param array $definitions the array of fixture definitions, as defined
   * in the YAML fixture file
   * @return array the Fixture instances, sorted by run order
   */
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
