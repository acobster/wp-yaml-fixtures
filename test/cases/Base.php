<?php
/**
 * Base class for YamlFixtures test cases
 *
 * @copyright 2018 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace YamlFixturesTest;

use PHPUnit\Framework\TestCase;
use Timber\User;
use WP_Mock;
use WP_Term;

/**
 * Base test class for the plugin. Declared abstract so that PHPUnit doesn't
 * complain about a lack of tests defined here.
 */
abstract class Base extends TestCase {
  public function setUp() {
    WP_Mock::setUp();
  }

  public function tearDown() {
    WP_Mock::tearDown();
  }

  protected function getProtectedProperty($object, $name) {
    $reflection = new \ReflectionClass($object);
    $property   = $reflection->getProperty($name);
    $property->setAccessible(true);

    return $property->getValue($object);
  }

  protected function setProtectedProperty($object, $name, $value) {
    $reflection = new \ReflectionClass($object);
    $property   = $reflection->getProperty($name);
    $property->setAccessible(true);

    return $property->setValue($object, $value);
  }

  protected function callProtectedMethod($object, $name, $args = []) {
    $reflection = new \ReflectionClass($object);
    $method     = $reflection->getMethod($name);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $args);
  }
}
