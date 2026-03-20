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
namespace Sylius\Behat\Page\Admin\Payment;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function complete_payment_of_order_with_number(string $order_number): void
    {
        $this->get_field($order_number, 'actions')->press_button('Complete');
    }
    public function choose_state_to_filter(string $payment_state): void
    {
        $this->get_element('filter_state')->select_option($payment_state);
    }
    public function get_payment_state_by_order_number(string $order_number): string
    {
        return $this->get_field($order_number, 'state')->get_text();
    }
    public function is_payment_with_order_number_in_position(string $order_number, int $position): bool
    {
        $result = $this->get_element('payment_in_given_position', ['%position%' => $position, '%orderNumber%' => $order_number]);
        return $result !== null;
    }
    public function show_order_page_for_nth_payment(int $position): void
    {
        $this->get_order_link_for_row($position)->click_link('#');
    }
    public function show_payment_request_of_nth_payment(int $position): void
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_rows_with_fields($table, [])[$position];
        $field = $table_accessor->get_field_from_row($table, $row, 'actions');
        $field->find('css', '[data-test-show-action="List payment requests"]')->click();
    }
    public function choose_channel_filter(string $channel_name): void
    {
        $this->get_element('filter_channel')->select_option($channel_name);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_channel' => '#criteria_channel', 'filter_state' => '#criteria_state', 'payment_in_given_position' => 'table tbody tr:nth-child(%position%) td:contains("%orderNumber%")']);
    }
    protected function get_order_link_for_row(int $payment_number): Node_Element
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_rows_with_fields($table, [])[$payment_number];
        return $row->find('css', 'td:nth-child(2)');
    }
    protected function get_field(string $order_number, string $field_name): Node_Element
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['number' => $order_number]);
        return $table_accessor->get_field_from_row($table, $row, $field_name);
    }
}