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
namespace Sylius\Behat\Page\Admin\Product_Variant;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function get_on_hand_quantity_for(Product_Variant_Interface $product_variant): int
    {
        return (int) $this->get_element('on_hand_quantity', ['%id%' => $product_variant->get_id()])->get_text();
    }
    public function get_on_hold_quantity_for(Product_Variant_Interface $product_variant): int
    {
        try {
            return (int) $this->get_element('on_hold_quantity', ['%id%' => $product_variant->get_id()])->get_text();
        } catch (Element_Not_Found_Exception) {
            return 0;
        }
    }
    public function set_position(string $name, int $position): void
    {
        /** @var NodeElement $productVariantsRow */
        $product_variants_row = $this->get_element('table')->find('css', sprintf('tbody > tr:contains("%s")', $name));
        Assert::not_null($product_variants_row, 'There are no row with given product variant\'s name!');
        $product_variant_position = $product_variants_row->find('css', '.sylius-product-variant-position');
        Assert::not_null($product_variant_position, 'There are no position field in given row!');
        $product_variant_position->set_value($position);
    }
    public function save_positions(): void
    {
        $this->get_element('save_configuration_button')->press();
        $this->get_document()->wait_for(5, fn(): bool => null === $this->get_element('save_configuration_button')->find('css', '.loading'));
    }
    public function count_items_with_no_name(): int
    {
        return count($this->get_element('table')->find_all('css', '[data-test-missing-translation]'));
    }
    public function has_generate_variants_button(): bool
    {
        return $this->has_element('generate_variants_button');
    }
    public function go_to_variant_generation(): void
    {
        $this->get_element('generate_variants_button')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['generate_variants_button' => '[data-test-generate]', 'on_hand_quantity' => '[data-test-on-hand][data-product-variant-id="%id%"]', 'on_hold_quantity' => '[data-test-on-hold][data-product-variant-id="%id%"]', 'save_configuration_button' => '[data-test-save-configuration-button]']);
    }
}