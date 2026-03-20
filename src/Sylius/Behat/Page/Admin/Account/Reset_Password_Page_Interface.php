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
namespace Sylius\Behat\Page\Admin\Account;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Reset_Password_Page_Interface extends Sylius_Page_Interface
{
    public function check_validation_message_for(string $element, string $message): bool;
    public function get_validation_message_for_new_password(): string;
    public function specify_new_password(string $password): void;
    public function specify_password_confirmation(string $password): void;
}