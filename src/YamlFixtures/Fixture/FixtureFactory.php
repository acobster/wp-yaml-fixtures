<?php

/**
 * FixtureFactory class
 */

namespace YamlFixtures\Fixture;

/**
 * Class for creating Fixtures from name and definition
 */
class FixtureFactory {
  /**
   * Types of fixture classes, keyed by name
   *
   * @var array
   */
  const TYPES = [
    'name'        => OptionsFixture::class,
    'blank_slate' => BlankSlateFixture::class,
    'posts'       => PostsFixture::class,
    'taxonomies'  => TaxonomiesFixture::class,
    'users'       => UsersFixture::class,
  ];

  /**
   * Create a fixture of type $key with the given definition.
   *
   * @param string $key the type of Fixture to instantiate
   * @param array $definition the YAML-defined assoc array for this fixture
   * @return Fixture a new Fixture instance
   */
  public static function create(string $key, $definition) {
    $class = static::TYPES[$key] ?? OptionsFixture::class;

    return new $class($key, $definition);
  }
}
