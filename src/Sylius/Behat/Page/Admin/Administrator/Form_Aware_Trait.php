<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Behat\Page\Admin\Administrator;

use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
trait Form_Aware_Trait
{
    use Secure_Password_Trait;
    public function set_first_name(string $first_name): void
    {
        $this->get_element('field_first_name')->set_value($first_name);
    }
    public function get_first_name(): string
    {
        return $this->get_element('field_first_name')->get_value();
    }
    public function set_last_name(string $last_name): void
    {
        $this->get_element('field_last_name')->set_value($last_name);
    }
    public function get_last_name(): string
    {
        return $this->get_element('field_last_name')->get_value();
    }
    public function set_username(string $username): void
    {
        $this->get_element('field_username')->set_value($username);
    }
    public function get_username(): string
    {
        return $this->get_element('field_username')->get_value();
    }
    public function set_email(string $email): void
    {
        $this->get_element('field_email')->set_value($email);
    }
    public function get_email(): string
    {
        return $this->get_element('field_email')->get_value();
    }
    public function set_password(string $password): void
    {
        $this->get_element('field_password')->set_value($this->replace_with_secure_password($password));
    }
    public function get_password(): string
    {
        return $this->get_element('field_password')->get_value();
    }
    public function set_locale(string $locale): void
    {
        $this->get_element('field_locale_code')->set_value($locale);
    }
    public function get_locale(): string
    {
        return $this->get_element('field_locale_code')->get_value();
    }
    public function enable(): void
    {
        $this->get_element('field_enabled')->check();
    }
    public function disable(): void
    {
        $this->get_element('field_enabled')->uncheck();
    }
    public function is_enabled(): bool
    {
        return $this->get_element('field_enabled')->get_value();
    }
    public function is_avatar_attached(): bool
    {
        return $this->get_element('avatar_image')->get_attribute('data-test-avatar-image') !== '';
    }
    public function attach_avatar(string $path): void
    {
        $files_path = $this->get_parameter('files_path');
        $avatar_field = $this->get_element('field_avatar');
        $avatar_field->attach_file($files_path . $path);
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_form_elements(): array
    {
        return ['avatar_image' => '[data-test-avatar-image]', 'field_avatar' => '#sylius_admin_admin_user_avatar_file', 'field_email' => '#sylius_admin_admin_user_email', 'field_enabled' => '#sylius_admin_admin_user_enabled', 'field_first_name' => '#sylius_admin_admin_user_firstName', 'field_last_name' => '#sylius_admin_admin_user_lastName', 'field_locale_code' => '#sylius_admin_admin_user_localeCode', 'field_name' => '#sylius_admin_admin_user_username', 'field_password' => '#sylius_admin_admin_user_plainPassword', 'field_username' => '#sylius_admin_admin_user_username'];
    }
}