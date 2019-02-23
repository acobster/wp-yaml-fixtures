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
    $this->remove_posts();
    $this->remove_terms();
    $this->remove_users();

    return true;
  }

  /**
   * Removes all posts from the database, unless explicitly configured not to.
   */
  protected function remove_posts() {
    if (!$this->should_remove_posts()) {
      return;
    }

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
  }

  /**
   * Whether to remove posts. Defaults to true unless explicitly set to
   * `false` (the actual value, not just a falsey value) by the user
   *
   * @return bool
   */
  protected function should_remove_posts() : bool {
    return ($this->definition['posts'] ?? true) !== false;
  }

  /**
   * Removes all terms from the database, unless explicitly configured not to.
   */
  protected function remove_terms() {
    if (!$this->should_remove_terms()) {
      return;
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
  }

  /**
   * Whether to remove terms. Defaults to true unless explicitly set to
   * `false` (the actual value, not just a falsey value) by the user
   *
   * @return bool
   */
  protected function should_remove_terms() : bool {
    return ($this->definition['terms'] ?? true) !== false;
  }

  /**
   * Removes all users from the database, unless explicitly configured not to.
   */
  protected function remove_users() {
    if (!$this->should_remove_users()) {
      return;
    }

    $users = array_filter(get_users(), function(WP_User $user) {
      return !$this->preserve_user($user);
    });
    foreach ($users as $user) {
      wp_delete_user($user->ID);
    }
  }

  /**
   * Whether to remove users. Defaults to true unless explicitly set to
   * `false` (the actual value, not just a falsey value) by the user
   *
   * @return bool
   */
  protected function should_remove_users() : bool {
    return ($this->definition['users'] ?? true) !== false;
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
