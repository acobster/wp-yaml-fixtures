<?php

/**
 * ReadingFixtureGenerator class
 */

namespace YamlFixtures\FixtureGenerator;

use YamlFixtures\Fixture\FixtureFactory;
use YamlFixtures\Fixture\OptionsFixture;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use wpdb;

/**
 * Class for generating Fixture YAML from existing database state
 */
class ReadingFixtureGenerator implements FixtureGeneratorInterface {
  /**
   * Types of generator classes, keyed by WP table name
   *
   * @var array
   */
  const TABLE_SHORTHANDS = [
    'posts',
    'users',
    'options',
    'usermeta',
  ];

  /** @var \wpdb */
  protected $wpdb;

  /** @var array */
  protected $options;

  /** @var array */
  protected $recorded_queries;

  public function __construct(wpdb $wpdb, array $options = []) {
    $this->wpdb    = $wpdb;
    $this->options = $options;

    $this->recorded_queries = [];
  }

  /**
   * Generate a fixture from the accumulated queries.
   *
   * @return array the generated YAML structure
   */
  public function generate(array $params) : array {
    $fixture = [];
    $fixture = $this->merge_users($fixture, $params['users']);
    $fixture = $this->merge_posts($fixture);

    return $fixture;
  }

  protected function merge_users(array $fixture, array $params) : array {
    $users = get_users();

    if ($users) {
      $fixture['users'] = $users;
    }

    return $fixture;
  }

  protected function merge_posts(array $fixture) : array {
    $posts = get_posts();

    if ($posts) {
      $fixture['posts'] = $posts;
    }

    return $fixture;
  }
}
