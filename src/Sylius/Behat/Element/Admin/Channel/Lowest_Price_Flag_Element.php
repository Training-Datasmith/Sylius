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
namespace Sylius\Behat\Element\Admin\Channel;

use Sylius\Behat\Element\Sylius_Element;
class Lowest_Price_Flag_Element extends Sylius_Element implements Lowest_Price_Flag_Element_Interface
{
    public function enable(): void
    {
        $this->get_element('lowest_price_for_discounted_products_visible')->check();
    }
    public function disable(): void
    {
        $this->get_element('lowest_price_for_discounted_products_visible')->uncheck();
    }
    public function is_enabled(): bool
    {
        return $this->get_element('lowest_price_for_discounted_products_visible')->is_checked();
    }
    protected function get_defined_elements(): array
    {
        return ['lowest_price_for_discounted_products_visible' => '[data-test-lowest-price-for-discounted-products-visible]'];
    }
}