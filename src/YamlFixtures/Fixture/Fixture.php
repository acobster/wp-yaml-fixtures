<?php

namespace YamlFixtures\Fixture;

abstract class Fixture {
  /**
   * @var array
   */
  const RESERVED_NAMES = [
    'name' => 'blogname',
  ];

  /**
    * @var array
   */
  const NAMES = [];

  /**
    * @var array
   */
  const FILTERS = [];

  protected $key;

  protected $definition;

  /**
   * Install this fixture.
   *
   * @return bool true on success, false otherwise
   */
  abstract public function install() : bool;

  // TODO
  // abstract public function generate() : string;
  public function __construct(string $key, $definition) {
    $this->key        = $key;
    $this->definition = $definition;
  }

  protected function name($key) {
    return static::NAMES[$key] ?? $key;
  }

  protected function replace_names(array $definition) {
    $fields = array_reduce(array_keys($definition), function(
      array $args,
      string $key
    ) use ($definition) {
      $name  = $this->name($key);
      $value = $definition[$key];

      // see if a filter exists for this field
      $filter = static::FILTERS[$name] ?? null;
      $args[$name] = $this->filter_value($value, $filter);

      return $args;
    }, []);

    // apply defaults
    foreach (static::FILTERS as $name => $filter) {
      $fields[$name] = $fields[$name] ?? $this->filter_value(null, $filter);
    }

    return $fields;
  }

  protected function filter_value($value, $filter) {
    if (is_callable($filter)) {
      return $filter($value);
    }

    // filter is a simple default value
    return $value ?? $filter;
  }

  /**
   * Filter out keys that are not present among `$keys`
   *
   * @param array $args the array to be filtered
   * @param array $keys a numeric array of keys to safelist
   * @return array a copy of `$args` filtered down to just keys present in
   * `$keys`
   */
  protected function filter_array_keys(array $args, array $keys) {
    return array_intersect_key($args, array_flip($keys));
  }
}
