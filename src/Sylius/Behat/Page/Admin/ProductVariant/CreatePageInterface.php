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

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
use Sylius\Component\Core\Model\Channel_Interface;
interface Create_Page_Interface extends Base_Create_Page_Interface
{
    public function specify_price(string $price, Channel_Interface $channel_name): void;
    public function specify_minimum_price(string $price, Channel_Interface $channel_name): void;
    public function specify_original_price(string $original_price, Channel_Interface $channel_name): void;
    public function specify_height_width_depth_and_weight(string $height, string $width, string $depth, string $weight): void;
    public function specify_code(string $code): void;
    public function specify_current_stock(string $current_stock): void;
    public function name_it_in(string $name, string $language): void;
    public function select_option(string $option_name, string $option_value): void;
    public function choose_pricing_calculator(string $name): void;
    public function get_validation_message_for_form(): string;
    public function select_shipping_category(string $shipping_category_name): void;
    public function get_prices_validation_message(): string;
    public function set_shipping_required(bool $is_shipping_required): void;
}