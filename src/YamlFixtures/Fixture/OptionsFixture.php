<?php

/**
 * OptionsFixture class
 */

namespace YamlFixtures\Fixture;

/**
 * Fixture for WP options
 */
class OptionsFixture extends Fixture {
  const NAMES = [
    'name'        => 'blogname',
    'description' => 'blogdescription',
  ];

  /**
   * Install this fixture
   *
   * @inheritdoc
   */
  public function install() : bool {
    update_option(
      $this->name($this->key),
      $this->definition
    );

    return true;
  }
}
