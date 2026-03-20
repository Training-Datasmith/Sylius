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
namespace Sylius\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Edit_To_Show_Page_Switcher_Interface;
use Sylius\Behat\Page\Admin\Show_Page_Button_Checker_Interface;
interface Update_Configurable_Product_Page_Interface extends Update_Page_Interface, Edit_To_Show_Page_Switcher_Interface, Show_Page_Button_Checker_Interface
{
    public function is_code_disabled(): bool;
    public function is_product_option_chosen(string $option): bool;
    public function is_product_options_disabled(): bool;
    public function check_channel(string $channel_code): void;
    public function go_to_variants_list(): void;
    public function go_to_variant_creation(): void;
    public function go_to_variant_generation(): void;
    public function has_tab(string $name): bool;
}