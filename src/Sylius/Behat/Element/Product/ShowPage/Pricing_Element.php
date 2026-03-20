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
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Element\Sylius_Element;
class Pricing_Element extends Sylius_Element implements Pricing_Element_Interface
{
    public function get_price_for_channel(string $channel_code): string
    {
        try {
            $channel_price_row = $this->get_channel_price_row($channel_code);
        } catch (Element_Not_Found_Exception) {
            return '';
        }
        $price_for_channel = $channel_price_row->find('css', '[data-test-price]');
        return $price_for_channel->get_text();
    }
    public function get_original_price_for_channel(string $channel_code): string
    {
        try {
            $channel_price_row = $this->get_channel_price_row($channel_code);
        } catch (Element_Not_Found_Exception) {
            return '';
        }
        $price_for_channel = $channel_price_row->find('css', '[data-test-original-price]');
        return $price_for_channel->get_text();
    }
    public function get_catalog_promotions_names_for_channel(string $channel_code): array
    {
        /** @var NodeElement[] $appliedPromotions */
        $applied_promotions = $this->get_applied_promotions_for_channel($channel_code);
        return array_map(fn(Node_Element $element): string => $element->get_text(), $applied_promotions);
    }
    public function get_catalog_promotion_links_for_channel(string $channel_code): array
    {
        $applied_promotions = $this->get_applied_promotions_for_channel($channel_code);
        return array_map(fn(Node_Element $element): string => $element->get_attribute('href'), $applied_promotions);
    }
    public function get_lowest_price_before_discount_for_channel(string $channel_code): string
    {
        $channel_price_row = $this->get_simple_product_pricing_row_for_channel($channel_code);
        if (null === $channel_price_row) {
            throw new \InvalidArgumentException(sprintf('Channel "%s" does not exist', $channel_code));
        }
        $price_for_channel = $channel_price_row->find('css', 'td:nth-child(4)');
        return $price_for_channel->get_text();
    }
    public function get_simple_product_pricing_row_for_channel(string $channel_code): Node_Element
    {
        return $this->get_element('simple_product_pricing_row', ['%channel_code%' => $channel_code]);
    }
    public function get_variant_pricing_row_for_channel(string $variant_code, string $channel_code): Node_Element
    {
        return $this->get_element('variant_pricing_row', ['%variant_code%' => $variant_code, '%channel_code%' => $channel_code]);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['price_row' => '[data-test-pricing="%channel_code%"]', 'simple_product_pricing_row' => '[data-test-simple-product="%channel_code%"]', 'variant_pricing_row' => '[data-test-variant-pricing="%channel_code%.%variant_code%"]']);
    }
    /** @return NodeElement[] */
    protected function get_applied_promotions_for_channel(string $channel_code): array
    {
        try {
            $channel_price_row = $this->get_channel_price_row($channel_code);
        } catch (Element_Not_Found_Exception) {
            return [];
        }
        return $channel_price_row->find_all('css', '[data-test-applied-promotion]');
    }
    /** @throws ElementNotFoundException */
    protected function get_channel_price_row(string $channel_code): Node_Element
    {
        return $this->get_element('price_row', ['%channel_code%' => $channel_code]);
    }
}