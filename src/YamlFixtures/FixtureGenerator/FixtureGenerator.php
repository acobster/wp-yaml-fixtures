<?php

/**
 * FixtureGeneratorFactory class
 */

namespace YamlFixtures\FixtureGenerator;

use YamlFixtures\Fixture\FixtureFactory;

use PHPSQLParser\PHPSQLParser;
use wpdb;

/**
 * Class for generating Fixture YAML from (valid) SQL queries
 */
class FixtureGenerator implements FixtureGeneratorInterface {
  /**
   * Types of generator classes, keyed by WP table name
   *
   * @var array
   */
  const TABLE_SHORTHANDS = [
    'posts',
    'users',
    'options',
  ];

  /** @var \wpdb */
  protected $wpdb;

  /** @var array */
  protected $options;

  /** @var PHPSQLParser */
  protected $parser;

  /** @var array */
  protected $recorded_queries;

  public function __construct(wpdb $wpdb, array $options = []) {
    $this->wpdb    = $wpdb;
    $this->options = $options;
    $this->parser  = $options['parser'] ?? new PHPSQLParser();

    $this->recorded_queries = [];
  }

  /**
   * Generate a fixture from the accumulated queries.
   *
   * @return array the generated YAML structure
   */
  public function generate() : array {
    if (empty($this->get_recorded_queries())) {
      return [];
    }

    $parsedQueryTrees = array_map(
      [$this->parser, 'parse'],
      $this->get_recorded_queries()
    );

    // start with a blank fixture and build it up from our query trees
    return array_reduce(
      $parsedQueryTrees,
      [$this, 'merge_query_tree_into_fixture'],
      []
    );
  }

  public function record_query(string $sql) {
    $this->recorded_queries[] = $sql;
  }

  /**
   * Get all recorded queries
   *
   * @return string[]
   */
  public function get_recorded_queries() : array {
    return $this->recorded_queries;
  }

  protected function merge_query_tree_into_fixture(array $fixture, array $tree) : array {
    // TODO handle UPDATE ...somehow??
    if (!isset($tree['INSERT'])) {
      // this isn't a database write
      return $fixture;
    }

    //print_r($tree['SET']);die();
    // find the table expression
    foreach ($tree['INSERT'] as $expr) {
      if ($expr['expr_type'] === 'table') {
        $table = $expr['alias'] ?: $expr['table'];
        $key = $this->table_key($table);

        $fixture[$key] = $fixture[$key] ?? [];
      }

      if ($expr['expr_type'] === 'column-list') {
        $columns = $this->exprs_to_values($expr['sub_tree']);
      }
    }

    // normalize inserted records
    if (isset($tree['VALUES'])) {
      $fixtureKeys = $this->column_shorthands($key, $columns);

      $entities = array_map(function(array $record) use($fixtureKeys) : array {
        return array_combine(
          $fixtureKeys,
          $this->exprs_to_values($record['data'])
        );
      }, $tree['VALUES']);

      $fixture[$key] = array_merge($fixture[$key], $entities);

    } elseif (isset($tree['SET'])) {

      $assigns = array_reduce($tree['SET'], function(array $acc, array $expr) : array {
        foreach ($expr['sub_tree'] as $sub) {
          if ($sub['expr_type'] === 'colref') {
            $column = $sub['base_expr'];
          } elseif ($sub['expr_type'] === 'const') {
            $value = $sub['base_expr'];
          }
        }

        if ( ! (isset($column) && isset($value)) ) {
          throw new \InvalidArgument(sprintf(
            "Couldn't determine column/value pair for expression: %s",
            var_export($expr, true)
          ));
        }

        $acc[$column] = trim($value, '\'"');

        return $acc;
      }, []);

      $entity = array_combine(
        $this->column_shorthands($key, array_keys($assigns)),
        array_values($assigns)
      );

      $fixture[$key][] = $entity;
    }

    return $fixture;
  }

  protected function table_key(string $table) : string {
    foreach (static::TABLE_SHORTHANDS as $key) {
      if ($this->wpdb->$key === $table) {
        return $key;
      }
    }

    throw new InvalidArgumentException("Unsupported table: $table");
  }

  protected function exprs_to_values(array $exprs) : array {
    return array_map(function(array $expr) : string {
      return trim($expr['base_expr'], '\'"'); // trim quotes
    }, $exprs);
  }

  protected function column_shorthands(string $tableKey, array $columns) : array {
    $fixtureClass = FixtureFactory::TYPES[$tableKey] ?? OptionsFixture::class;
    $mappings = array_flip($fixtureClass::NAMES);

    return array_map(function(string $col) use($mappings) : string {
      return $mappings[$col] ?? $col;
    }, $columns);
  }
}
