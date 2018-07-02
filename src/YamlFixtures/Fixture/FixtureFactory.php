<?php

namespace YamlFixtures\Fixture;

class FixtureFactory {
  const TYPES = [
    'name'        => OptionsFixture::class,
    'blank_slate' => BlankSlateFixture::class,
    'posts'       => PostsFixture::class,
    'taxonomies'  => TaxonomiesFixture::class,
    'users'       => UsersFixture::class,
  ];

  public static function create(string $key, $definition) {
    $class = static::TYPES[$key] ?? OptionsFixture::class;

    return new $class($key, $definition);
  }
}
