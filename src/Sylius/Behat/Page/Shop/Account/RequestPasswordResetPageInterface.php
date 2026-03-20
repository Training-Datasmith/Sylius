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
interface Request_Password_Reset_Page_Interface extends Sylius_Page_Interface
{
    public function check_validation_message_for(string $element, string $message): bool;
    public function reset(): void;
    public function specify_email(?string $email): void;
}