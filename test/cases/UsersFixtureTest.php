<?php

/**
 * Tests for the YamlFixtures\Fixture\UsersFixture class
 */

namespace YamlFixturesTest;

use WP_Mock;
use WP_Mock\Functions;

use YamlFixtures\Fixture\UsersFixture;

class UserFixtureTest extends Base {
  public function test_install() {
    // expect user "dusty" to be inserted w/ ID 123
    WP_Mock::userFunction('wp_insert_user', [
      'times'   => 1,
      'args'    => [
        [
          'user_email'  => 'dusty@example.com',
          'user_login'  => 'dusty',
          'user_pass'   => 'passw0rd',
        ],
      ],
      'return'  => 123,
    ]);

    // expect user "rusty" to be inserted w/ ID 456
    WP_Mock::userFunction('wp_insert_user', [
      'times'     => 1,
      'args'      => [
        Functions::type('array'),
        // TODO fix/implement recursive array checks in wp_mock, so this works:
        //[
        //  'user_email'  => 'rusty@example.com',
        //  'user_login'  => 'rusty',
        //  'user_pass'   => Functions::type('string'),
        //],
      ],
      'return'    => 456,
    ]);

    // expect add_user_meta to be called for rusty's user
    WP_Mock::userFunction('add_user_meta', [
      'times'   => 1,
      'args'    => [456, 'favorite_food', 'chocolate'],
    ]);
    WP_Mock::userFunction('add_user_meta', [
      'times'   => 1,
      'args'    => [456, 'favorite_power_ranger', 'yellow'],
    ]);

    $fixture = new UsersFixture('user', [
      [
        'email'    => 'dusty@example.com',
        'login'    => 'dusty',
        'password' => 'passw0rd',
      ],
      [
        'email'    => 'rusty@example.com',
        'login'    => 'rusty',
        'meta'     => [
          'favorite_food'         => 'chocolate',
          'favorite_power_ranger' => 'yellow',
        ],
      ],
    ]);

    $this->assertTrue($fixture->install());
  }
}
