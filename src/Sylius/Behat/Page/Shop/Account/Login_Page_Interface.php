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
namespace Sylius\Behat\Page\Shop\Account;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Login_Page_Interface extends Sylius_Page_Interface
{
    public function has_validation_error_with(string $message): bool;
    public function log_in(): void;
    public function specify_password(string $password): void;
    public function specify_username(string $username): void;
}