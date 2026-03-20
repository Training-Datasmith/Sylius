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
namespace Sylius\Behat\Page\Admin\Shipment;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function choose_state_to_filter(string $shipment_state): void;
    public function choose_channel_filter(string $channel_name): void;
    public function choose_shipping_method_filter(string $shipping_method_name): void;
    public function is_shipment_with_order_number_in_position(string $order_number, int $position): bool;
    public function ship_shipment_of_order_with_number(string $order_number): void;
    public function get_shipment_status_by_order_number(string $order_number): string;
    public function show_order_page_for_nth_shipment(int $position): void;
    public function ship_shipment_of_order_with_tracking_code(string $order_number, string $tracking_code): void;
    public function get_shipped_at_date(string $order_number): string;
}