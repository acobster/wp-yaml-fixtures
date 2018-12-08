<?php

/**
 * HomePageSlugFixture class
 */

namespace YamlFixtures\Fixture;

/**
 * Fixture for setting the home page ("page_on_front") WP option
 */
class HomePageSlugFixture extends Fixture {
  /**
   * Install this fixture
   *
   * @inheritdoc
   */
  public function install() : bool {
    $homePageId = $this->slug_to_id($this->definition);

    if ($homePageId) {
      update_option(
        'page_on_front',
        $homePageId
      );

      // Override the "Your latest posts" home-page setting
      update_option(
        'show_on_front',
        'page'
      );
    }

    return true;
  }

  /**
   * Get a post ID from a slug.
   *
   * @param string $slug the post slug
   * @return int the post ID or 0 if not found
   */
  protected function slug_to_id(string $slug) : int {
    // get the first post matching $slug, default to null
    $post = get_posts([
      'name'      => $slug,
      'post_type' => 'page',
    ])[0] ?? null;

    // return an int whether we found a post or not
    return (int) ($post->ID ?? 0);
  }
}
