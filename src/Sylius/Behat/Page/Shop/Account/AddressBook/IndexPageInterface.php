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
namespace Sylius\Behat\Page\Shop\Account\Address_Book;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Index_Page_Interface extends Sylius_Page_Interface
{
    public function get_addresses_count(): int;
    public function has_address_of(string $full_name): bool;
    public function has_no_addresses(): bool;
    public function has_no_default_address(): bool;
    /**
     * @throws \InvalidArgumentException
     */
    public function get_full_name_of_default_address(): string;
    public function address_of_contains(string $full_name, string $value): bool;
    public function edit_address(string $full_name): void;
    public function delete_address(string $full_name): void;
    public function set_as_default(string $full_name): void;
}