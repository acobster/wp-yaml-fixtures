<?php

/**
 * Fixture base class
 */

namespace YamlFixtures\Fixture;

/**
 * Abstract class on which all fixtures are based.
 * Custom fixtures should extend this class.
 */
abstract class Fixture {
  /**
   * YAML keys to be considered reserved
   *
   * @var array
   */
  const RESERVED_NAMES = [
    'name' => 'blogname',
  ];

  /**
   * A map of shorthand values to their WP counterparts,
   * e.g. "title" => "post_title"
   *
   * @var array
   */
  const NAMES = [];

  /**
   * Filters and default values to apply to specific keys,
   * e.g. "post_status" => "publish"
   *
   * @var array
   */
  const FILTERS = [];

  /**
   * The key (name) of this fixture
   */
  protected $key;

  /**
   * The definition of this filter, as specified in YAML
   */
  protected $definition;

  /**
   * Install this fixture.
   *
   * @return bool true on success, false otherwise
   */
  abstract public function install() : bool;

  /**
   * Constructor
   *
   * @param string $key the name of this fixture (corresponding to a YAML key)
   * @param array an associative array of fixture data
   */
  public function __construct(string $key, $definition) {
    $this->key        = $key;
    $this->definition = $definition;
  }

  /**
   * Convert a YAML key to a name that WP understands
   * e.g. "title" => "post_title"
   */
  protected function name($key) {
    return static::NAMES[$key] ?? $key;
  }

  /**
   * Apply filters to the filter definition
   */
  protected function replace_names(array $definition) {
    $fields = array_reduce(array_keys($definition), function(
      array $args,
      string $key
    ) use ($definition) {
      $name  = $this->name($key);
      $value = $definition[$key];

      // see if a filter exists for this field
      $filter      = static::FILTERS[$name] ?? null;
      $args[$name] = $this->filter_value($value, $filter);

      return $args;
    }, []);

    // apply defaults
    foreach (static::FILTERS as $name => $filter) {
      $fields[$name] = $fields[$name] ?? $this->filter_value(null, $filter);
    }

    return $fields;
  }

  /**
   * Filter a single value
   *
   * @param mixed $value the value to filter
   * @param mixed $filter the default value, or, if $filter is a callable,
   * the result of calling $filter($value) is returned.
   */
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
