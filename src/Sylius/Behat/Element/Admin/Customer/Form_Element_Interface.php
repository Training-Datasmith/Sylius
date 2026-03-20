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
namespace Sylius\Behat\Element\Admin\Customer;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function specify_first_name(string $name): void;
    public function specify_last_name(string $name): void;
    public function specify_email(string $email): void;
    public function specify_birthday(string $birthday): void;
    public function specify_password(string $password): void;
    public function choose_gender(string $gender): void;
    public function choose_group(string $group): void;
    public function get_full_name(): string;
    public function get_first_name(): string;
    public function get_last_name(): string;
    public function enable(): void;
    public function disable(): void;
    public function get_password(): string;
    public function subscribe_to_the_newsletter(): void;
    public function is_subscribed_to_the_newsletter(): bool;
    public function get_group_name(): string;
    public function verify_user(): void;
}