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
namespace Sylius\Behat\Client;

interface Api_Security_Client_Interface
{
    public function prepare_login_request(): void;
    public function set_email(string $email): void;
    public function set_password(string $password): void;
    public function call(): void;
    public function is_logged_in(): bool;
    public function get_error_message(): string;
    public function log_out(): void;
}