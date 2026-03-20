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
namespace Sylius\Behat\Page\Admin;

use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Model\Product_Interface;
interface Dashboard_Page_Interface extends Sylius_Page_Interface
{
    public function get_total_sales(): string;
    public function get_number_of_paid_orders(): int;
    public function get_number_of_new_orders_in_the_list(): int;
    public function get_number_of_new_customers(): int;
    public function get_number_of_new_customers_in_the_list(): int;
    public function get_average_order_value(): string;
    public function get_dashboard_header(): string;
    public function log_out(): void;
    public function choose_channel(string $channel_name): void;
    public function choose_year_split_by_months_interval(): void;
    public function choose_month_split_by_days_interval(): void;
    public function choose_previous_period(): void;
    public function choose_next_period(): void;
    public function search_for_product_via_navbar(Product_Interface $product_name): void;
    public function get_number_of_orders_to_process(): int;
    public function get_number_of_pending_payments(): int;
    public function get_number_of_product_reviews_to_approve(): int;
    public function get_number_of_product_variants_out_of_stock(): int;
    public function get_number_of_shipments_to_ship(): int;
}