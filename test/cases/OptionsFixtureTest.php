<?php

/**
 * Tests for the YamlFixtures\Fixture\OptionsFixture class
 */

namespace YamlFixturesTest;

use WP_Mock;

use YamlFixtures\Fixture\OptionsFixture;

/**
 * Test OptionsFixture
 *
 * @group unit
 */
class OptionsFixtureTest extends Base {
  public function test_install_blogname() {
    $fixture = new OptionsFixture('name', 'the option value');

    WP_Mock::userFunction('update_option', [
      'times' => 1,
      'args'  => ['blogname', 'the option value'],
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_arbitrary_option() {
    $fixture = new OptionsFixture('some_setting', 'the option value');

    WP_Mock::userFunction('update_option', [
      'times' => 1,
      'args'  => ['some_setting', 'the option value'],
    ]);

    $this->assertTrue($fixture->install());
  }
}

