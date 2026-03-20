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

interface Form_Aware_Interface
{
    public function set_first_name(string $first_name): void;
    public function get_first_name(): string;
    public function set_last_name(string $last_name): void;
    public function get_last_name(): string;
    public function set_username(string $username): void;
    public function get_username(): string;
    public function set_email(string $email): void;
    public function get_email(): string;
    public function set_password(string $password): void;
    public function get_password(): string;
    public function set_locale(string $locale): void;
    public function get_locale(): string;
    public function enable(): void;
    public function disable(): void;
    public function is_enabled(): bool;
    public function attach_avatar(string $path): void;
    public function is_avatar_attached(): bool;
}