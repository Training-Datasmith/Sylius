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
namespace Sylius\Behat\Page\Admin\Product_Variant;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function disable_tracking(): void;
    public function enable_tracking(): void;
    public function is_code_disabled(): bool;
    public function is_selected_option_value_on_page(string $option_name, string $value_name): bool;
    public function is_shipping_required(): bool;
    public function is_tracked(): bool;
    public function get_pricing_configuration_for_channel_and_currency_calculator(Channel_Interface $channel, Currency_Interface $currency): string;
    public function get_price_for_channel(Channel_Interface $channel): string;
    public function get_minimum_price_for_channel(Channel_Interface $channel): string;
    public function get_original_price_for_channel(Channel_Interface $channel): string;
    public function get_name_in_language(string $language): string;
    public function select_option(string $option_name, string $option_value): void;
    public function is_show_in_shop_button_disabled(): bool;
    public function show_product_in_channel(Channel_Interface $channel): void;
    public function show_product_in_single_channel(): void;
    public function specify_current_stock(int $amount): void;
    public function specify_price(int $price, ?Channel_Interface $channel_name = null): void;
    public function specify_original_price(?int $original_price, ?Channel_Interface $channel = null): void;
    public function disable(): void;
    public function is_enabled(): bool;
    public function enable(): void;
}