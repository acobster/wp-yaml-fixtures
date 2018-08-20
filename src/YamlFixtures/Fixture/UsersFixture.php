<?php

namespace YamlFixtures\Fixture;

use RandomLib\Factory;

class UsersFixture extends Fixture {
  /**
   * The character set to user for generating random passwords
   */
  const PASSWORD_CHARACTERS =
    '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/!@#$%^&*():;';

  const NAMES = [
    'email'    => 'user_email',
    'login'    => 'user_login',
    'email'    => 'user_email',
    'password' => 'user_pass',
  ];

  /**
   * Array keys to safelist before passing to wp_insert_user()
   *
   * @var array
   */
  const USER_INSERT_FIELDS = [
    'ID',
		'user_pass',
		'user_login',
		'user_nicename',
		'user_url',
		'user_email',
		'display_name',
		'nickname',
		'first_name',
		'last_name',
		'description',
		'rich_editing',
		'syntax_highlighting',
		'comment_shortcuts',
		'admin_color',
		'use_ssl',
		'user_registered',
		'show_admin_bar_front',
		'role',
		'locale',
  ];

  public function install() : bool {
    foreach ($this->definition as $user) {
      $this->replace_names($user);
      $id = $this->insert_user($user);
      $this->insert_meta_fields($id, $user);
    }

    return true;
  }

  /**
   * Sanitize and insert the given user data as a new WP user
   *
   * @param array $user the user data to be inserted
   */
  protected function insert_user(array $user) {
    $user['password'] = $user['password'] ?? $this->generate_password();

    return (int) wp_insert_user(
      $this->filter_array_keys(
        $this->replace_names($user),
        static::USER_INSERT_FIELDS
      )
    );
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

  protected function generate_password() {
    return (new Factory())->getMediumStrengthGenerator()->generateString(32);
  }
}
