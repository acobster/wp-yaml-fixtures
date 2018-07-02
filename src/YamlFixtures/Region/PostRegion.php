<?php

namespace YamlFixtures\Region;

use Dictator\Regions\Region;

class PostRegion extends Region {
  public function impose( $key, $value ) {
    WP_CLI::success(implode(',', [$key, $value]));
  }

  public function get_differences() {
    return [];
  }
}
