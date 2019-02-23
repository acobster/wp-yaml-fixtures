<?php

/**
 * Tests for the YamlFixtures\Fixture\BlankSlateFixture class
 */

namespace YamlFixturesTest;

use WP_Mock;
use WP_Mock\Functions;

use YamlFixtures\Fixture\BlankSlateFixture;

/**
 * Test BlankSlateFixture
 *
 * @group unit
 */
class BlankSlateFixtureTest extends Base {
  public function test_remove_all() {
    $fixture = new BlankSlateFixture('blank_slate', [
      'all' => true,
    ]);

    WP_Mock::userFunction('get_post_types', [
      'times'  => 1,
      'return' => ['post', 'page'],
    ]);

    WP_Mock::userFunction('get_post_stati', [
      'return' => ['publish', 'pending', 'future'],
    ]);

    $this->mock_post_deletions(range(1, 3), [
      // TODO query??
    ]);


    WP_Mock::userFunction('get_taxonomies', [
      'times'  => 1,
      'return' => ['category', 'post_tag'],
    ]);

    $term1 = new \stdclass;
    $term1->term_id = 1;
    $term2 = new \stdclass;
    $term2->term_id = 2;
    WP_Mock::userFunction('get_terms', [
      'times' => 1,
      'args'  => [
        [
          'taxonomy' => 'category',
          'hide_empty' => false,
        ],
      ],
      'return' => [$term1, $term2],
    ]);

    $term3 = new \stdclass;
    $term3->term_id = 3;
    $term4 = new \stdclass;
    $term4->term_id = 4;
    WP_Mock::userFunction('get_terms', [
      'times' => 1,
      'args'  => [
        [
          'taxonomy' => 'post_tag',
          'hide_empty' => false,
        ],
      ],
      'return' => [$term3, $term4],
    ]);

    WP_Mock::userFunction('wp_delete_term', [
      'times' => 1,
      'args'  => [1, 'category'],
    ]);
    WP_Mock::userFunction('wp_delete_term', [
      'times' => 1,
      'args'  => [2, 'category'],
    ]);
    WP_Mock::userFunction('wp_delete_term', [
      'times' => 1,
      'args'  => [3, 'post_tag'],
    ]);
    WP_Mock::userFunction('wp_delete_term', [
      'times' => 1,
      'args'  => [4, 'post_tag'],
    ]);

    $this->assertTrue($fixture->install());
  }

  protected function mock_post_deletions($ids) {
    $posts = [];
    foreach ($ids as $id) {
      $post     = new \stdclass;
      $post->ID = $id;
      $posts[]  = $post;

      WP_Mock::userFunction('wp_delete_post', [
        'times' => 1,
        'args'  => [$id],
      ]);
    }

    WP_Mock::userFunction('get_posts', [
      'return' => $posts,
      'args'   => [
        [
          'post_type' => 'post',
          'numberposts' => -1,
          'post_status' => ['publish', 'pending', 'future'],
        ],
      ],
    ]);
    WP_Mock::userFunction('get_posts', [
      'return' => $posts,
      'args'   => [
        [
          'post_type' => 'page',
          'numberposts' => -1,
          'post_status' => ['publish', 'pending', 'future'],
        ],
      ],
    ]);
  }

  protected function mock_term_deletions($ids) {
    foreach ($ids as $id) {
      WP_Mock::userFunction('wp_delete_term', [
        'times' => 1,
        'args'  => [$id],
      ]);
    }
  }
}
