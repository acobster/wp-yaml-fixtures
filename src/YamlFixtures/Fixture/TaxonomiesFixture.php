<?php

namespace YamlFixtures\Fixture;

class TaxonomiesFixture extends Fixture {
  const RESERVED_NAMES = [
    'name' => 'blogname',
  ];

  public function install() : bool {
    foreach ($this->definition as $tax => $terms) {
      $this->insert_terms($tax, $terms);
    }

    return true;
  }

  protected function insert_terms(string $tax, array $terms) {
    $keys = [
      'alias_of',
      'description',
      'parent',
      'slug',
    ];

    foreach ($terms as $term) {
      $name = $term['name'];
      $args = array_intersect_key($term, array_flip($keys));

      wp_insert_term($name, $tax, $args);
    }
  }
}
