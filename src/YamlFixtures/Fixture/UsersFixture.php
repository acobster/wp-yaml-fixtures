<?php

namespace YamlFixtures\Fixture;

class UsersFixture extends Fixture {
  const NAMES = [
    'email'    => 'user_email',
    'login'    => 'user_login',
    'email'    => 'user_email',
    'password' => 'user_pass',
  ];

  public function install() : bool {
    foreach ($this->definition as $user) {
      $this->replace_names($user);
      $id = wp_insert_user($this->replace_names($user));

      $this->insert_meta_fields($id, $user);
    }

    return true;
  }

  protected function insert_meta_fields(int $id, array $user) {
    if (!isset($user['meta'])) {
      return;
    }

    foreach ($user['meta'] as $key => $value) {
      $this->add_user_meta($id, $key, $value);
    }
  }

  protected function add_user_meta(int $id, string $key, $value) {
    if (!is_array($value)) {
      $value = [$value];
    }

    foreach ($value as $v) {
      add_user_meta($id, $key, $v);
    }
  }
}
