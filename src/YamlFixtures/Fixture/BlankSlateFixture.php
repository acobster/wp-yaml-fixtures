<?php

/**
 * BlankSlateFixture class
 */

namespace YamlFixtures\Fixture;

use WP_User;

/**
 * Class for wiping WP database of users, posts, etc.
 */
class BlankSlateFixture extends Fixture {
  /**
   * Install this fixture.
   *
   * @inheritdoc
   */
  public function install() : bool {
    // TODO just call WP_CLI db reset
    foreach (get_post_types() as $type) {
      $posts = get_posts([
        'post_type'   => $type,
        'numberposts' => -1,
        'post_status' => get_post_stati(),
      ]);

      foreach ($posts as $post) {
        wp_delete_post($post->ID);
      }
    }

    foreach (get_taxonomies() as $tax) {
      $terms = get_terms([
        'taxonomy'   => $tax,
        'hide_empty' => false,
      ]);

      foreach ($terms as $term) {
        wp_delete_term($term->term_id, $tax);
      }
    }

    $users = array_filter(get_users(), function(WP_User $user) {
      return !$this->preserve_user($user);
    });
    foreach ($users as $user) {
      wp_delete_user($user->ID);
    }

    return true;
  }

  /**
   * Whether to preserve the given WP_User from the blank-slate wipe
   *
   * @param WP_User $user the user in question
   * @return bool
   */
  protected function preserve_user(WP_User $user) {
    $exemptions = $this->definition['preserve_users'] ?? null;
    if (!$exemptions) {
      // don't preserve any users
      return false;
    }

    return in_array($user->ID, $exemptions, true)
      || in_array($user->user_login, $exemptions, true)
      || in_array($user->user_email, $exemptions, true);
  }
}
