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
class Checkout_Subtotal_Element extends Sylius_Element implements Checkout_Subtotal_Element_Interface
{
    public function get_product_quantity(string $product_name): int
    {
        return (int) $this->get_element('item_quantity', ['%name%' => $product_name])->get_text();
    }
    protected function get_defined_elements(): array
    {
        return ['item_quantity' => '[data-test-item-subtotal-quantity="%name%"]'];
    }
}