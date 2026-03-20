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

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
use Sylius\Behat\Page\Admin\Edit_To_Show_Page_Switcher_Interface;
use Sylius\Behat\Page\Admin\Show_Page_Button_Checker_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
interface Update_Simple_Product_Page_Interface extends Base_Update_Page_Interface, Edit_To_Show_Page_Switcher_Interface, Show_Page_Button_Checker_Interface
{
    public function is_code_disabled(): bool;
    public function disable_tracking(): void;
    public function enable_tracking(): void;
    public function is_tracked(): bool;
    public function is_shipping_required(): bool;
    public function go_to_variants_list(): void;
    public function go_to_variant_creation(): void;
    public function has_generate_variants_button(): bool;
    public function go_to_variant_generation(): void;
    public function has_tab(string $name): bool;
    public function get_show_product_in_single_channel_url(): string;
    public function is_show_in_shop_button_disabled(): bool;
    public function show_product_in_channel(Channel_Interface $channel): void;
    public function show_product_in_single_channel(): void;
    public function disable(): void;
    public function is_enabled(): bool;
    public function enable(): void;
}