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
class Lowest_Price_Information_Element extends Sylius_Element implements Lowest_Price_Information_Element_Interface
{
    public function is_there_information_about_product_lowest_price_with_price(string $lowest_price_before_discount): bool
    {
        return $this->has_element('lowest_price_information_element_with_price', ['%lowestPriceBeforeDiscount%' => $lowest_price_before_discount]);
    }
    public function is_there_information_about_product_lowest_price(): bool
    {
        return $this->has_element('lowest_price_information_element') && $this->get_element('lowest_price_information_element')->is_visible();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['lowest_price_information_element' => '[data-test-lowest-price-before-discount]:contains("The lowest price of this product from")', 'lowest_price_information_element_with_price' => '[data-test-lowest-price-before-discount]:contains("%lowestPriceBeforeDiscount%")']);
    }
}