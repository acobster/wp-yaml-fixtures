<?php

/**
 * FixtureFileGenerator class
 */

namespace YamlFixtures\FixtureGenerator;

use InvalidArgumentException;
use PHPSQLParser\PHPSQLParser;
use Symfony\Component\Yaml\Yaml;
use wpdb;

/**
 * Class for generating Fixture YAML files
 */
class FixtureFileGenerator extends FixtureGenerator {
  /**
   * output_file option is required.
   * @inheritdoc
   */
  public function __construct(wpdb $wpdb, array $options = []) {
    parent::__construct($wpdb, $options);

    if (empty($options['output_file'])) {
      throw new InvalidArgumentException('No output_file specified!');
    }
  }

  /**
   * Generate a fixture from the accumulated queries.
   *
   * @return array the generated YAML structure
   */
  public function generate() : array {
    $structure = parent::generate();

    file_put_contents($this->options['output_file'], Yaml::dump($structure));

    return $structure;
  }
}
