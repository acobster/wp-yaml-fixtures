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
    ]);

    $fixture = new UsersFixture('user', [
      [
        'email'    => 'dusty@example.com',
        'login'    => 'dusty',
        'password' => 'passw0rd',
      ],
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_multiple() {
    // expect exactly three insertions
    WP_Mock::userFunction('wp_insert_user', [
      'times'   => 1,
      'args'    => [
        [
          'user_email'  => 'dusty@example.com',
          'user_login'  => 'dusty',
          'user_pass'   => 'passw0rd',
        ],
      ],
    ]);
    WP_Mock::userFunction('wp_insert_user', [
      'times'   => 1,
      'args'    => [
        [
          'user_email'  => 'rusty@example.com',
          'user_login'  => 'rusty',
          'user_pass'   => 'passw0rd',
        ],
      ],
    ]);
    WP_Mock::userFunction('wp_insert_user', [
      'times'   => 1,
      'args'    => [
        [
          'user_email'  => 'krusty@example.com',
          'user_login'  => 'krusty',
          'user_pass'   => 'heyK!ds',
        ],
      ],
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
        'password' => 'passw0rd',
      ],
      [
        'email'    => 'krusty@example.com',
        'login'    => 'krusty',
        'password' => 'heyK!ds',
      ],
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_with_meta() {
    // expect user "rusty" to be inserted w/ ID 456
    WP_Mock::userFunction('wp_insert_user', [
      'times'     => 1,
      'args'      => [
        Functions::type('array'),
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
