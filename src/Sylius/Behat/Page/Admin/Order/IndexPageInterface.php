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
namespace Sylius\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function specify_filter_date_from(string $date_time): void;
    public function specify_filter_date_to(string $date_time): void;
    public function specify_filter_channel(string $channel_name): void;
    public function specify_filter_shipping_method(string $method_name): void;
    public function choose_filter_currency(string $currency_name): void;
    public function specify_filter_total_greater_than(string $total): void;
    public function specify_filter_total_less_than(string $total): void;
    public function specify_filter_product(string $product_name): void;
    public function specify_filter_variant(string $variant_name): void;
    public function specify_filter_customer(string $customer_name): void;
}