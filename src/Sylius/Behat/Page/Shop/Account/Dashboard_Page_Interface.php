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

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Dashboard_Page_Interface extends Page_Interface
{
    public function has_customer_name(string $name): bool;
    public function has_customer_email(string $email): bool;
    public function is_verified(): bool;
    public function has_resend_verification_email_button(): bool;
    public function press_resend_verification_email(): void;
}