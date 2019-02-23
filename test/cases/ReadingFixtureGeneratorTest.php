<?php

/**
 * Tests for the YamlFixtures\FixtureGenerator\ReadingFixtureGenerator class
 */

namespace YamlFixturesTest;

use WP_Mock;
use WP_Mock\Functions;

use YamlFixtures\FixtureGenerator\ReadingFixtureGenerator;

/**
 * Test ReadingFixtureGenerator
 *
 * @group unit
 */
class ReadingFixtureGeneratorTest extends Base {
  /** @var \wbdb */
  private $wpdb;

  /** @var ReadingFixtureGenerator */
  private $generator;

  public function setUp() {
    parent::setUp();

    $this->wpdb = $this->mockWpdb();
    $this->generator = new ReadingFixtureGenerator($this->wpdb);
  }

  public function test_generate_empty() {
    WP_Mock::userFunction('get_users', [
      'times'  => 1,
      'return' => [],
    ]);
    WP_Mock::userFunction('get_posts', [
      'times'  => 1,
      'return' => [],
    ]);
    // TODO support options

    $this->assertEquals([], $this->generator->generate());
  }

  public function test_generate_users() {
    WP_Mock::userFunction('get_users', [
      'times'  => 1,
      'return' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ]);

    WP_Mock::userFunction('get_posts', [
      'times'  => 1,
      'return' => [],
    ]);

    $this->assertEquals([
      'users' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ], $this->generator->generate());
  }

  public function test_generate_users_from_ids() {
    WP_Mock::userFunction('get_users', [
      'times'  => 1,
      'args'   => [
        ['include' => [1, 2]],
      ],
      'return' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ]);

    WP_Mock::userFunction('get_posts', [
      'times'  => 1,
      'return' => [],
    ]);

    $this->assertEquals([
      'users' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ], $this->generator->generate([
      'users' => [1, 2],
    ]));
  }

  public function test_generate_users_from_logins() {
    WP_Mock::userFunction('get_users', [
      'times'  => 1,
      'return' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ]);

    WP_Mock::userFunction('get_posts', [
      'times'  => 1,
      'return' => [],
    ]);

    $this->assertEquals([
      'users' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ], $this->generator->generate());
  }

  public function test_generate_users_from_emails() {
    WP_Mock::userFunction('get_users', [
      'times'  => 1,
      'return' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ]);

    WP_Mock::userFunction('get_posts', [
      'times'  => 1,
      'return' => [],
    ]);

    $this->assertEquals([
      'users' => [
        [
          'email' => 'spongebob@bikinibottom.gov',
          'login' => 'spongebob',
          'role'  => 'burger_flipper',
          'meta'  => [
            'first_name' => 'Spongebob',
            'last_name'  => 'Squarepants',
          ],
        ],
        [
          'email' => 'squidward@squidwardvoice.band',
          'login' => 'squidward',
          'role'  => 'singer',
          'meta'  => [
            'first_name' => 'Squidward',
            'last_name'  => 'Tentacles',
          ],
        ],
      ],
    ], $this->generator->generate());
  }
}
