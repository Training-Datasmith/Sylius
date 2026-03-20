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
use Sylius\Component\Core\Model\Address_Interface;
interface Create_Page_Interface extends Sylius_Page_Interface
{
    public function fill_address_data(Address_Interface $address): void;
    public function select_country(string $name): void;
    public function add_address(): void;
    public function has_province_validation_message(): bool;
    public function count_validation_messages(): int;
}