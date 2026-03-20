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
namespace Sylius\Behat\Page\Admin\Product;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Context\Ui\Admin\Helper\Navigation_Trait;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Component\Core\Model\Product_Variant_Interface;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    use Navigation_Trait;
    public function get_resource_name(): string
    {
        return 'product';
    }
    public function is_simple_product_page(): bool
    {
        return !$this->has_element('variants');
    }
    public function is_show_in_shop_button_disabled(): bool
    {
        return $this->get_element('show_product_button')->has_class('disabled');
    }
    /** @return string[] */
    public function get_applied_catalog_promotions_links(string $variant_name, string $channel_name): array
    {
        $applied_promotions = $this->get_applied_catalog_promotions($variant_name, $channel_name);
        return array_map(fn(Node_Element $element): string => $element->get_attribute('href'), $applied_promotions);
    }
    /** @return string[] */
    public function get_applied_catalog_promotions_names(string $variant_name, string $channel_name): array
    {
        $applied_promotions = $this->get_applied_catalog_promotions($variant_name, $channel_name);
        return array_map(fn(Node_Element $element): string => $element->get_text(), $applied_promotions);
    }
    public function get_name(): string
    {
        return $this->get_element('product_name')->get_text();
    }
    public function get_breadcrumb(): string
    {
        return $this->get_element('breadcrumb')->get_text();
    }
    public function get_route_name(): string
    {
        return 'sylius_admin_product_show';
    }
    public function show_product_in_channel(string $channel): void
    {
        $this->get_element('show_product_button')->click_link($channel);
    }
    public function show_product_in_single_channel(): void
    {
        $this->get_element('show_product_button')->click();
    }
    public function show_variant_edit_page(Product_Variant_Interface $variant): void
    {
        $this->get_element('edit_variant_button', ['%variant_code%' => $variant->get_code()])->click();
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['breadcrumb' => '.breadcrumb', 'edit_product_button' => '[data-test-edit-product]', 'edit_variant_button' => '[data-test-edit-variant="%variant_code%"]', 'product_name' => '[data-test-product-name]', 'show_product_button' => '[data-test-view-in-store]', 'variants' => '[data-test-variant-pricing]']);
    }
    /** @return NodeElement[] */
    protected function get_applied_catalog_promotions(string $variant_name, string $channel_name): array
    {
        $pricing_element = $this->get_pricing_row($variant_name, $channel_name);
        return $pricing_element->find_all('css', '.applied-promotion');
    }
    protected function get_pricing_row(string $variant_name, string $channel_name): Node_Element
    {
        /** @var NodeElement|null $pricingRow */
        $pricing_row = $this->get_document()->find('css', sprintf('tr:contains("%s") + tr', $variant_name));
        $pricing_row = $pricing_row->find('css', sprintf('td:contains("%s")', $channel_name));
        if ($pricing_row === null) {
            throw new \InvalidArgumentException(sprintf('Cannot find pricing row for variant "%s" in channel "%s"', $variant_name, $channel_name));
        }
        return $pricing_row;
    }
}