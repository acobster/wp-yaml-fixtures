<?php

namespace YamlFixtures\Fixture;

class OptionsFixture extends Fixture {
  const NAMES = [
    'name'        => 'blogname',
    'description' => 'blogdescription',
  ];

  public function install() : bool {
    update_option(
      $this->name($this->key),
      $this->definition
    );

    return true;
  }

  protected function name($name) {
    return static::NAMES[$name] ?? $name;
  }
}
