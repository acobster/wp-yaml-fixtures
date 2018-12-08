<?php

namespace YamlFixtures\Fixture;

class PostsFixture extends Fixture {
  /**
   * Supported wp_posts column names. Note that not all native WP names are
   * included, e.g. post_category. These are handled in other ways (with terms, meta, and possible others)
   */
  const WP_POST_COLUMNS = [
    'post_author',
    'post_date',
    'post_date_gmt',
    'post_content',
    'post_content_filtered',
    'post_title',
    'post_excerpt',
    'post_status',
    'post_type',
    'comment_status',
    'ping_status',
    'post_password',
    'post_name',
    'to_ping',
    'pinged',
    'post_modified',
    'post_modified_gmt',
    'post_parent',
    'menu_order',
    'post_mime_type',
    'post_category',
  ];

  const NAMES = [
    'title'   => 'post_title',
    'slug'    => 'post_name',
    'type'    => 'post_type',
    'content' => 'post_content',
    'excerpt' => 'post_excerpt',
    'date'    => 'post_date',
    'author'  => 'post_author',
    'parent'  => 'post_parent',
    'status'  => 'post_status',
  ];

  const FILTERS = [
    'post_status' => 'publish',
    'post_author' => [self::class, 'any_author'],
  ];

  /**
   * Maintain a mapping of slugs to IDs so we can inject
   * post hierarchy at insert time
   *
   * @var array
   */
  protected $slug_to_id_map = [];

  public function install() : bool {
    $posts = array_map([$this, 'replace_names'], $this->definition);

    foreach ($posts as $args) {
      $args['post_name'] = $args['post_name']
        ?? sanitize_title($args['post_title']);

      if (isset($args['post_parent'])) {
        // determine the actual ID of the parent
        $args['post_parent'] = $this->slug_to_id($args['post_parent']);
      }

      // safelist down to supported column names
      $post = array_intersect_key(
        $args,
        array_flip(static::WP_POST_COLUMNS)
      );

      // create the post
      $id = wp_insert_post($post);

      // save a mapping of the new post's slug to its ID
      $this->slug_to_id_map[$args['post_name']] = $id;

      $this->insert_post_meta($id, $args);
      $this->set_post_terms($id, $args);
    }

    return true;
  }

  protected function insert_post_meta(int $id, array $args) {
    if (!isset($args['meta'])) {
      return;
    }

    foreach ($args['meta'] as $key => $value) {
      $this->add_post_meta($id, $key, $value);
    }
  }

  protected function add_post_meta(int $id, string $key, $value) {
    if (!is_array($value)) {
      $value = [$value];
    }

    foreach ($value as $v) {
      add_post_meta($id, $key, $v);
    }
  }

  protected function set_post_terms(int $id, array $args) {
    if (!isset($args['terms'])) {
      return;
    }

    foreach ($args['terms'] as $tax => $terms) {
      wp_set_post_terms($id, $terms, $tax, false);
    }
  }

  protected static function any_author($id) {
    if ($id) {
      return $id;
    }

    $users = get_users([
      'role__in' => ['administrator', 'editor', 'author', 'contributor'],
      'number'   => 1,
    ]);

    return $users[0]->ID ?? 1;
  }

  protected function slug_to_id(string $slug) {
    return $this->slug_to_id_map[$slug] ?? null;
  }
}
