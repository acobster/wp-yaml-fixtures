<?php

/**
 * Tests for the YamlFixtures\Fixture\UsersFixture class
 */

namespace YamlFixturesTest;

use WP_Mock;
use WP_Mock\Functions;

use YamlFixtures\Fixture\PostsFixture;

class PostsFixtureTest extends Base {
  public function test_install() {
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Hello, Fixture!',
        'slug'    => 'hello-fixtures',
        'content' => 'Lorem ipsum dolor sit amet',
        'author'  => 1,
      ],
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Hello, Fixture!',
          'post_name'    => 'hello-fixtures',
          'post_content' => 'Lorem ipsum dolor sit amet',
          'post_author'  => 1,
          'post_status'  => 'publish',
        ],
      ],
      'return' => 123,
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_with_parent_slug() {
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Parent Post',
        'slug'    => 'parent-post-slug',
        'content' => 'This is the parent post',
        'author'  => 1,
      ],
      [
        'title'   => 'Hello, Fixture!',
        'slug'    => 'hello-fixtures',
        'content' => 'This is the child post',
        'author'  => 1,
        'parent'  => 'parent-post-slug',
      ],
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Parent Post',
          'post_name'    => 'parent-post-slug',
          'post_content' => 'This is the parent post',
          'post_author'  => 1,
          'post_status'  => 'publish',
        ],
      ],
      'return' => 123,
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Hello, Fixture!',
          'post_name'    => 'hello-fixtures',
          'post_content' => 'This is the child post',
          'post_author'  => 1,
          'post_status'  => 'publish',
          'post_parent'  => 123,
        ],
      ],
      'return' => 456,
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_without_slug() {
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Hello, Fixture!',
        'content' => 'Lorem ipsum dolor sit amet',
        'author'  => 1,
      ],
    ]);

    WP_Mock::userFunction('sanitize_title', [
      'times'  => 1,
      'args'   => ['Hello, Fixture!'],
      'return' => 'sanitized-slug',
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Hello, Fixture!',
          'post_name'    => 'sanitized-slug',
          'post_content' => 'Lorem ipsum dolor sit amet',
          'post_author'  => 1,
          'post_status'  => 'publish',
        ],
      ],
      'return' => 123,
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_with_meta() {
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Hello, Fixture!',
        'content' => 'Lorem ipsum dolor sit amet',
        'slug'    => 'hello-fixture',
        'author'  => 1,
        'meta'    => [
          'goo'   => 'OOEY GOOEY',
          'glue'  => 'GOOEY GLUEY',
        ],
      ],
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'return' => 123,
    ]);

    WP_Mock::userFunction('add_post_meta', [
      'times' => 1,
      'args'  => [123, 'goo', 'OOEY GOOEY'],
    ]);
    WP_Mock::userFunction('add_post_meta', [
      'times' => 1,
      'args'  => [123, 'glue', 'GOOEY GLUEY'],
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_with_meta_array() {
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Hello, Fixture!',
        'content' => 'Lorem ipsum dolor sit amet',
        'slug'    => 'hello-fixture',
        'author'  => 1,
        'meta'    => [
          'multifield' => ['VALUE 1', 'VALUE 2', 'VALUE 3'],
        ],
      ],
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'return' => 123,
    ]);

    WP_Mock::userFunction('add_post_meta', [
      'times' => 1,
      'args'  => [123, 'multifield', 'VALUE 1'],
    ]);
    WP_Mock::userFunction('add_post_meta', [
      'times' => 1,
      'args'  => [123, 'multifield', 'VALUE 2'],
    ]);
    WP_Mock::userFunction('add_post_meta', [
      'times' => 1,
      'args'  => [123, 'multifield', 'VALUE 3'],
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_install_with_terms() {
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Hello, Fixture!',
        'author'  => 1,
        'terms'   => [
          'category' => [
            'my-category',
            'some-other-category',
          ],
          'custom_taxonomy' => [
            'special',
            'but-also-not-so-special',
          ],
        ],
      ],
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'return' => 123,
    ]);

    WP_Mock::userFunction('wp_set_post_terms', [
      'times' => 1,
      'args'  => [
        123,
        [
          'my-category',
          'some-other-category',
        ],
        'category',
        false,
      ],
    ]);
    WP_Mock::userFunction('wp_set_post_terms', [
      'times' => 1,
      'args'  => [
        123,
        [
          'special',
          'but-also-not-so-special',
        ],
        'custom_taxonomy',
        false,
      ],
    ]);

    $this->assertTrue($fixture->install());
  }
}
