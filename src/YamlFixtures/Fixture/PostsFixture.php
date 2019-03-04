<?php

/**
 * PostsFixture class
 */

namespace YamlFixtures\Fixture;

/**
 * Fixture for WP posts of all types
 */
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

  /**
   * Install this fixture
   *
   * @inheritdoc
   */
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
      if (!isset($this->slug_to_id_map[$args['post_name']])) {
        $this->slug_to_id_map[$args['post_name']] = $id;
      }

      $this->insert_post_meta($id, $args);
      $this->set_post_terms($id, $args);
    }

    return true;
  }

  /**
   * Insert post metadata
   *
   * @param int $id the post ID
   * @param array $args the post data. If no `meta` key is present,
   * this method does nothing.
   */
  protected function insert_post_meta(int $id, array $args) {
    if (empty($args['meta']) && empty($args['associations'])) {
      return;
    }

    // build associations and merge them with hard-coded meta fields
    $meta = array_merge(
      $args['meta'] ?? [],
      $this->build_associations($args['associations'] ?? [])
    );

    foreach ($meta as $key => $value) {
      $this->add_post_meta($id, $key, $value);
    }
  }

  protected function build_associations(array $fields) : array {
    return array_reduce($fields, function($data, $association) {
      if (!isset($association['meta_key'])) {
        return $data;
      }

      $data[$association['meta_key']] = $this->build_association($association);

      return $data;
    }, []);
  }

  protected function build_association(array $definition) {
    // TODO other types of associations via Factory
    $user = get_user_by('email', $definition['email']);

    return $user->ID;
  }

  /**
   * Adds data for meta field `$key`.
   * If `$value` is an array, inserts a row per index.
   *
   * @param int $id the post ID
   * @param string $key the meta key
   * @param mixed $value the meta value(s) to add for $key
   */
  protected function add_post_meta(int $id, string $key, $value) {
    if (!is_array($value)) {
      $value = [$value];
    }

    foreach ($value as $v) {
      add_post_meta($id, $key, $v);
    }
  }

  /**
   * Insert terms for this post.
   *
   * @param int $id the post ID
   * @param array $args the post data. If no `terms` key is present,
   * this method does nothing.
   */
  protected function set_post_terms(int $id, array $args) {
    if (!isset($args['terms'])) {
      return;
    }

    foreach ($args['terms'] as $tax => $terms) {
      wp_set_post_terms($id, $terms, $tax, false);
    }
  }

  /**
   * Returns the post_author ID `$id`, or if `$id` is blank,
   * the ID of the first WP user it finds
   *
   * @param int|null $id the post_author
   * @return int
   */
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

  /**
   * Get the ID corresponding to the slug of a previously inserted post
   *
   * @param string $slug the slug to lookup
   * @return int|null
   */
  protected function slug_to_id(string $slug) {
    return $this->slug_to_id_map[$slug] ?? null;
  }
}
