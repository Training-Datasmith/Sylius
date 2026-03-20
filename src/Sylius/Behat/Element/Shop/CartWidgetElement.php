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
namespace Sylius\Behat\Element\Shop;

use Sylius\Behat\Element\Sylius_Element;
class Cart_Widget_Element extends Sylius_Element implements Cart_Widget_Element_Interface
{
    public function get_cart_total_quantity(): int
    {
        if (!$this->has_element('cart_quantity')) {
            return 0;
        }
        $element = $this->get_element('cart_quantity');
        $attribute_value = $element->get_attribute('data-test-cart-quantity');
        return is_numeric($attribute_value) ? (int) $attribute_value : 0;
    }
    protected function get_defined_elements(): array
    {
        return ['cart_quantity' => '[data-test-cart-quantity]'];
    }
}