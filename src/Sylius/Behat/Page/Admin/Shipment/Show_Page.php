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

use Sylius\Behat\Page\Sylius_Page;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_admin_shipment_show';
    }
    public function get_amount_of_units(string $product_name): int
    {
        $items = $this->get_element('items');
        return count($items->find_all('css', sprintf('[data-test-item="%s"]', $product_name)));
    }
    public function get_state(): string
    {
        return $this->get_element('state')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['items' => '[data-test-table-items]', 'state' => '[data-test-shipment-state]']);
    }
}