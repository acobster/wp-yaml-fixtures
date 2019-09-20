<?php

/**
 * Tests for the YamlFixtures\Fixture\UsersFixture class
 */

namespace YamlFixturesTest;

use WP_Mock;
use WP_Mock\Functions;

use YamlFixtures\Fixture\PostsFixture;

/**
 * Test PostsFixture
 *
 * @group unit
 */
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

  public function test_install_with_duplicate_slugs() {
    /*
     * WordPress will gracefully handle duplicate slugs at INSERT time by
     * appending unique integers. However, Fixtures can't tell when a slug
     * is overridden in this way just by calling wp_insert_post. For this
     * reason, we need to make sure we are not overwriting duplicate slugs
     * in the slug->id mapping that Fixtures track internally.
     */
    $fixture = new PostsFixture('post', [
      [
        'title'   => 'Parent',
        'slug'    => 'duplicate-slug',
        'content' => 'This is the parent post',
        'author'  => 1,
      ],
      [
        'title'   => 'Son',
        'slug'    => 'duplicate-slug',
        'content' => 'This is a child post',
        'author'  => 1,
        'parent'  => 'duplicate-slug',
      ],
      [
        'title'   => 'Daughter',
        'slug'    => 'duplicate-slug',
        'content' => 'This is another child post',
        'author'  => 1,
        'parent'  => 'duplicate-slug',
      ],
    ]);

    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Parent',
          'post_name'    => 'duplicate-slug',
          'post_content' => 'This is the parent post',
          'post_author'  => 1,
          'post_status'  => 'publish',
        ],
      ],
      'return' => 123,
    ]);

    /*
     * Assert that both children get the correct post_parent ID
     */
    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Son',
          'post_name'    => 'duplicate-slug',
          'post_content' => 'This is a child post',
          'post_author'  => 1,
          'post_status'  => 'publish',
          'post_parent'  => 123,
        ],
      ],
      'return' => 456,
    ]);
    WP_Mock::userFunction('wp_insert_post', [
      'times' => 1,
      'args'  => [
        [
          'post_title'   => 'Daughter',
          'post_name'    => 'duplicate-slug',
          'post_content' => 'This is another child post',
          'post_author'  => 1,
          'post_status'  => 'publish',
          'post_parent'  => 123,
        ],
      ],
      'return' => 789,
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

    /*
     * The correct thing to pass to wp_set_post_terms() depends on whether
     * the taxonomy is hierarchical, because of course it does. From the docs:
     *
     * "If you want to enter terms of a hierarchical taxonomy like categories,
     * then use IDs. If you want to add non-hierarchical terms like tags, then
     * use names. "
     *
     * https://codex.wordpress.org/Function_Reference/wp_set_post_terms
     *
     * For this reason, we need to first call is_taxonomy_hierarchical() for
     * each taxonomy block within this post.
     */
    WP_Mock::userFunction('is_taxonomy_hierarchical', [
      'times' => 1,
      'args'  => ['category'],
      'return' => true,
    ]);
    WP_Mock::userFunction('get_terms', [
      'times' => 1,
      'args'  => [
        [
          'taxonomy'   => 'category',
          'hide_empty' => false,
          'slug'       => ['my-category', 'some-other-category'],
          'fields'     => 'names',
        ],
      ],
      'return' => ['My Category', 'Some Other Category'], // return term names
    ]);
    WP_Mock::userFunction('wp_set_post_terms', [
      'times' => 1,
      'args'  => [
        123,
        ['My Category', 'Some Other Category'], // pass term names
        'category',
        false,
      ],
    ]);

    WP_Mock::userFunction('is_taxonomy_hierarchical', [
      'times' => 1,
      'args'  => ['custom_taxonomy'],
      'return' => false,
    ]);
    WP_Mock::userFunction('get_terms', [
      'times' => 1,
      'args'  => [
        [
          'taxonomy'   => 'custom_taxonomy',
          'hide_empty' => false,
          'slug'       => ['special', 'but-also-not-so-special'],
          'fields'     => 'ids',
        ],
      ],
      'return' => [456, 789], // return term IDs
    ]);
    WP_Mock::userFunction('wp_set_post_terms', [
      'times' => 1,
      'args'  => [
        123,
        [456, 789], // pass term IDs
        'custom_taxonomy',
        false,
      ],
    ]);

    $this->assertTrue($fixture->install());
  }

  public function test_post_install_with_associated_user() {
    WP_Mock::userFunction('wp_insert_post', [
      'times'  => 1,
      'return' => 123,
    ]);

    // mock our user ID
    $user     = new \stdclass();
    $user->ID = 3;

    WP_Mock::userFunction('get_user_by', [
      'times'  => 1,
      'args'   => [
        'email',
        'me@example.com',
      ],
      'return' => $user,
    ]);

    WP_Mock::userFunction('add_post_meta', [
      'times' => 1,
      'args'  => [
        123,
        'associated_user',
        3, // this is the ID we got from get_user_by
      ],
    ]);

    $fixture = new PostsFixture('post', [
      [
        'title'        => 'My Frist Post',
        'slug'         => 'my-frist-post',
        'author'       => 1,
        'associations' => [
          [
            'meta_key'   => 'associated_user',
            'type'       => 'user',
            'email'      => 'me@example.com',
          ],
        ],
      ],
    ]);

    $this->assertTrue($fixture->install());
  }
}
