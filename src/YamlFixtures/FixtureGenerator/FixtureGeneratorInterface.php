<?php

/**
 * FixtureGeneratorInterface
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace YamlFixtures\FixtureGenerator;

/**
 * Interface for generating YAML fixtures from queries, data, etc.
 */
interface FixtureGeneratorInterface {
  public function generate() : array;
}
