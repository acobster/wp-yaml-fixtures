<?php

namespace YamlFixtures\State;

use Dictator\States\State as Dictatorship;

use YamlFixtures\Region\PostRegion;

class ContentState extends Dictatorship {
  protected $regions = [
    'posts' => PostRegion::class,
  ];
}
