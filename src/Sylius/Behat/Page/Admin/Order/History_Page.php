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

use Sylius\Behat\Page\Sylius_Page;
class History_Page extends Sylius_Page implements History_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_admin_order_history';
    }
    public function count_billing_address_changes(): int
    {
        return count($this->get_element('billing_address_logs')->find_all('css', '[data-test-address-log]'));
    }
    public function count_shipping_address_changes(): int
    {
        return count($this->get_element('shipping_address_logs')->find_all('css', '[data-test-address-log]'));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['billing_address_logs' => '[data-test-address-type="Billing address"]', 'shipping_address_logs' => '[data-test-address-type="Shipping address"]']);
    }
}