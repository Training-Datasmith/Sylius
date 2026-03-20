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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function choose_state_to_filter(string $shipment_state): void
    {
        $this->get_element('filter_state')->select_option($shipment_state);
    }
    public function choose_channel_filter(string $channel_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('filter_channel')->get_xpath(), $channel_name);
        $this->wait_for_form_update();
    }
    public function choose_shipping_method_filter(string $shipping_method_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('filter_shipping_method')->get_xpath(), $shipping_method_name);
        $this->wait_for_form_update();
    }
    public function is_shipment_with_order_number_in_position(string $order_number, int $position): bool
    {
        $rows = $this->get_table_accessor()->get_indexed_column($this->get_element('table'), 'order');
        $order_from_row = $rows[$position - 1] ?? null;
        return $order_from_row !== null && str_contains($order_from_row, $order_number);
    }
    public function ship_shipment_of_order_with_number(string $order_number): void
    {
        $this->get_field($order_number, 'actions')->press_button('Ship');
    }
    public function get_shipment_status_by_order_number(string $order_number): string
    {
        return $this->get_field($order_number, 'state')->get_text();
    }
    public function show_order_page_for_nth_shipment(int $position): void
    {
        $this->get_order_link_for_row($position)->click_link('#');
    }
    public function ship_shipment_of_order_with_tracking_code(string $order_number, string $tracking_code): void
    {
        /** @var NodeElement $actions */
        $actions = $this->get_field($order_number, 'actions');
        $actions->fill_field('sylius_admin_shipment_ship_tracking', $tracking_code);
        $actions->press_button('Ship');
    }
    public function get_shipped_at_date(string $order_number): string
    {
        return $this->get_field($order_number, 'shippedAt')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_channel' => '#criteria_channel', 'filter_shipping_method' => '#criteria_method', 'filter_state' => '#criteria_state']);
    }
    protected function get_field(string $order_number, string $field_name): Node_Element
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['order' => $order_number]);
        return $table_accessor->get_field_from_row($table, $row, $field_name);
    }
    protected function get_order_link_for_row(int $shipment_number): Node_Element
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_rows_with_fields($table, [])[$shipment_number];
        return $row->find('css', 'td:nth-child(3)');
    }
}