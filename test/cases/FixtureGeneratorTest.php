<?php

/**
 * Tests for the YamlFixtures\FixtureGenerator\FixtureGenerator class
 */

namespace YamlFixturesTest;

use WP_Mock;
use WP_Mock\Functions;

use YamlFixtures\FixtureGenerator\FixtureGenerator;

/**
 * Test FixtureGenerator
 *
 * @group unit
 */
class FixtureGeneratorTest extends Base {
  /** @var \wbdb */
  private $wpdb;

  /** @var FixtureGenerator */
  private $generator;

  public function setUp() {
    parent::setUp();

    $this->wpdb = $this->mockWpdb();
    $this->generator = new FixtureGenerator($this->wpdb);
  }

  public function test_generate_empty() {
    $this->assertEquals([], $this->generator->generate());
  }

  public function test_generate_with_values_clauses() {

    $this->generator->record_write(
      'INSERT INTO wp_posts (post_author, post_type) VALUES (456, "post"), (123, "page")'
    );

    $this->assertEquals([
      'posts' => [
        [
          'author' => 456,
          'type'   => 'post',
        ],
        [
          'author' => 123,
          'type'   => 'page',
        ],
      ],
    ], $this->generator->generate());

  }

  public function test_generate_with_set_clause() {
    $this->generator->record_write(
      'INSERT INTO wp_posts SET post_author = 123, post_type = "post"'
    );
    $this->generator->record_write(
      'INSERT INTO wp_posts SET post_author = 123, post_type = "page", post_title = "A New Page"'
    );

    $this->assertEquals([
      'posts' => [
        [
          'author' => 123,
          'type'   => 'post',
        ],
        [
          'author' => 123,
          'type'   => 'page',
          'title'  => 'A New Page',
        ],
      ],
    ], $this->generator->generate());
  }

  public function test_generate_users() {
    $this->generator->record_write(
      'INSERT INTO wp_users (user_login, user_email, user_nicename) VALUES ("example", "me@example.com", "Me Myself")'
    );
    $this->generator->record_write(
      'INSERT INTO wp_users (user_login, user_email, user_nicename) VALUES ("spongebob", "spongebob@bikinibottom.com", "Spongebob Squarepants")'
    );

    $this->assertEquals([
      'users' => [
        [
          'login'    => 'example',
          'email'    => 'me@example.com',
          'nicename' => 'Me Myself',
        ],
        [
          'login'    => 'spongebob',
          'email'    => 'spongebob@bikinibottom.com',
          'nicename' => 'Spongebob Squarepants',
        ],
      ],
    ], $this->generator->generate());
  }

  public function test_generate_terms() {
    $this->markTestSkipped();
  }
}
