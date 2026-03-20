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
interface Register_Page_Interface extends Sylius_Page_Interface
{
    public function get_route_name(): string;
    public function check_validation_message_for(string $element, string $message): bool;
    public function register(): void;
    public function specify_email(string $email): void;
    public function specify_first_name(string $first_name): void;
    public function specify_password(string $password): void;
    public function specify_phone_number(string $phone_number): void;
    public function verify_password(string $password): void;
    public function subscribe_to_the_newsletter(): void;
}