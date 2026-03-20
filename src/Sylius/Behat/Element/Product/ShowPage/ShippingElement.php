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
namespace Sylius\Behat\Element\Product\Show_Page;

use Sylius\Behat\Element\Sylius_Element;
class Shipping_Element extends Sylius_Element implements Shipping_Element_Interface
{
    public function get_product_shipping_category(): string
    {
        return $this->get_element('shipping_category')->get_text();
    }
    public function get_product_height(): float
    {
        return (float) $this->get_element('product_height')->get_text();
    }
    public function get_product_depth(): float
    {
        return (float) $this->get_element('product_depth')->get_text();
    }
    public function get_product_weight(): float
    {
        return (float) $this->get_element('product_weight')->get_text();
    }
    public function get_product_width(): float
    {
        return (float) $this->get_element('product_width')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['product_depth' => '[data-test-depth]', 'product_height' => '[data-test-height]', 'product_weight' => '[data-test-weight]', 'product_width' => '[data-test-width]', 'shipping_category' => '[data-test-shipping-category]']);
    }
}