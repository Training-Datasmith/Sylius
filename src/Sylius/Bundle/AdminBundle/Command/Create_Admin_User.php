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
namespace Sylius\Bundle\Admin_Bundle\Command;

final readonly class Create_Admin_User
{
    public function __construct(private string $email, private string $username, private ?string $first_name, private ?string $last_name, private string $plain_password, private string $locale_code, private bool $enabled)
    {
    }
    public function get_email(): string
    {
        return $this->email;
    }
    public function get_username(): string
    {
        return $this->username;
    }
    public function get_plain_password(): string
    {
        return $this->plain_password;
    }
    public function get_first_name(): ?string
    {
        return $this->first_name;
    }
    public function get_last_name(): ?string
    {
        return $this->last_name;
    }
    public function get_locale_code(): string
    {
        return $this->locale_code;
    }
    public function is_enabled(): bool
    {
        return $this->enabled;
    }
}