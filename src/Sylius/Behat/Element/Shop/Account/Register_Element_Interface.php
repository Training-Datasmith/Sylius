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
namespace Sylius\Behat\Element\Shop\Account;

interface Register_Element_Interface
{
    public function register(): void;
    public function specify_email(?string $email): void;
    public function get_email(): string;
    public function specify_first_name(?string $first_name): void;
    public function specify_last_name(?string $last_name): void;
    public function specify_password(string $password): void;
    public function specify_phone_number(string $phone_number): void;
    public function verify_password(string $password): void;
    public function subscribe_to_the_newsletter(): void;
    public function get_validation_message(string $element, array $parameters = []): string;
}