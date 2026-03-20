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

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Element\Sylius_Element;
class Variants_Element extends Sylius_Element implements Variants_Element_Interface
{
    public function count_variants_on_page(): int
    {
        /** @var NodeElement $variants|array */
        $variants = $this->get_document()->find_all('css', '[data-test-variant]');
        return \count($variants);
    }
    public function has_product_variant(string $code): bool
    {
        return $this->has_element('variant', ['%code%' => $code]);
    }
    public function has_product_variant_with_code_price_and_current_stock(string $name, string $code, string $price, string $current_stock, string $channel_code): bool
    {
        $variant_rows = $this->get_document()->find_all('css', '[data-test-variant]');
        /** @var NodeElement $variant */
        foreach ($variant_rows as $variant) {
            if ($this->has_product_with_given_name_code_price_and_current_stock($variant, $name, $code, $price, $current_stock, $channel_code)) {
                return true;
            }
        }
        return false;
    }
    public function has_product_variant_with_lowest_price_before_discount_in_channel(string $product_variant_code, string $lowest_price_before_discount, string $channel_code): bool
    {
        /** @var NodeElement $variant */
        $variant = $this->get_document()->find('css', sprintf('[data-test-lowest-price-before-the-discount="%s.%s"]', $product_variant_code, $channel_code));
        if ($variant) {
            return $variant->get_text() === $lowest_price_before_discount;
        }
        return false;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['variant' => '[data-test-variant="%code%"]', 'variant_pricing_row' => '[data-test-variant-pricing="%channel_code%.%variant_code%"]']);
    }
    protected function has_product_with_given_name_code_price_and_current_stock(Node_Element $variant, string $name, string $code, string $price, string $current_stock, string $channel_code): bool
    {
        if ($variant->find('css', '[data-test-product-variant-code]')->get_text() === $code && $variant->find('css', '[data-test-product-variant-name]')->get_text() === $name && $this->get_element('variant_pricing_row', ['%channel_code%' => $channel_code, '%variant_code%' => $code])->find('css', '[data-test-price]')->get_text() === $price && $variant->find('css', '[data-test-current-stock]')->get_text() === $current_stock) {
            return true;
        }
        return false;
    }
}