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
namespace Sylius\Behat\Page\Shop\Account\Order;

use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Sylius_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected Table_Accessor_Interface $table_accessor)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_account_order_index';
    }
    public function count_orders(): int
    {
        return $this->table_accessor->count_table_body_rows($this->get_element('customer_orders'));
    }
    public function change_payment_method(Order_Interface $order): void
    {
        $row = $this->table_accessor->get_row_with_fields($this->get_element('customer_orders'), ['number' => $order->get_number()]);
        $link = $row->find('css', '[data-test-button="pay"]');
        $link->click();
    }
    public function has_flash_message(string $message): bool
    {
        return str_contains($this->get_element('flash_message')->get_text(), $message);
    }
    public function is_order_with_number_in_the_list($number): bool
    {
        try {
            $rows = $this->table_accessor->get_rows_with_fields($this->get_element('customer_orders'), ['number' => $number]);
            return 1 === count($rows);
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
    public function open_last_order_page(): void
    {
        $this->get_element('last_order')->find('css', '[data-test-button="show"]')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['customer_orders' => '[data-test-grid-table]', 'flash_message' => '[data-test-sylius-flash-message]', 'last_order' => '[data-test-grid-table-body] [data-test-row]:last-child [data-test-actions]']);
    }
}