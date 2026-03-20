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
interface Update_Page_Interface extends Sylius_Page_Interface
{
    public function fill_field(string $field, ?string $value): void;
    public function get_specified_province(): string;
    public function get_selected_province(): string;
    public function specify_province(string $name): void;
    public function select_province(string $name): void;
    public function select_country(string $name): void;
    public function wait_for_form_to_stop_loading(): void;
    public function save_changes(): void;
}