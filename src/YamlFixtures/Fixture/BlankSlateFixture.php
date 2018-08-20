<?php

namespace YamlFixtures\Fixture;

use WP_User;

class BlankSlateFixture extends Fixture {
  const POST_STATUSES = [
    'publish',
    'pending',
        'draft',
        'auto-draft',
        'future',
        'private',
        'inherit',
        'trash',
  ];

  public function install() : bool {
    // TODO just call WP_CLI db reset
    foreach (get_post_types() as $type) {
      $posts = get_posts([
        'post_type'   => $type,
        'numberposts' => -1,
        'post_status' => static::POST_STATUSES,
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

  protected function preserve_user(WP_User $user) {
    $exemptions = $this->definition['preserve_users'] ?? null;
    if (!$exemptions) {
      // don't preserve any users
      return false;
    }

    return in_array($user->ID, $exemptions)
      || in_array($user->user_login, $exemptions)
      || in_array($user->user_email, $exemptions);
  }
}
