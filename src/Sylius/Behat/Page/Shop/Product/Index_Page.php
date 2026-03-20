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
namespace Sylius\Behat\Page\Shop\Product;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Sylius_Page;
class Index_Page extends Sylius_Page implements Index_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_product_index';
    }
    public function count_products_items(): int
    {
        $products_list = $this->get_element('products');
        $products = $products_list->find_all('css', '[data-test-product]');
        return count($products);
    }
    public function get_first_product_name_from_list(): string
    {
        $products_list = $this->get_element('products');
        return $products_list->find('css', '[data-test-product]:first-child [data-test-product-name]')->get_text();
    }
    public function get_last_product_name_from_list(): string
    {
        $products_list = $this->get_element('products');
        return $products_list->find('css', '[data-test-product]:last-child [data-test-product-content] [data-test-product-name]')->get_text();
    }
    public function search(string $name): void
    {
        $this->get_document()->fill_field('criteria_search_value', $name);
        $this->get_element('search_button')->submit();
    }
    public function sort(string $order_number): void
    {
        $this->get_document()->click_link($order_number);
    }
    public function clear_filter(): void
    {
        $this->get_element('clear')->click();
    }
    public function is_product_on_list(string $product_name): bool
    {
        try {
            $this->get_element('product_name', ['%productName%' => $product_name]);
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        return true;
    }
    public function is_empty(): bool
    {
        return str_contains($this->get_element('flash_message')->get_text(), 'There are no results to display');
    }
    public function get_product_price(string $product_code): string
    {
        $element = $this->get_element('product', ['%productCode%' => $product_code]);
        return $element->find('css', '[data-test-product-price]')->get_text();
    }
    public function get_product_original_price(string $product_code): ?string
    {
        $element = $this->get_element('product', ['%productCode%' => $product_code]);
        $original_price_element = $element->find('css', '[data-test-product-original-price]');
        return $original_price_element !== null ? $original_price_element->get_text() : null;
    }
    public function get_product_promotion_label(string $product_name): ?string
    {
        $element = $this->get_element('product_name', ['%productName%' => $product_name]);
        $promotion_label_element = $element->get_parent()->get_parent()->find('css', '[data-test-promotion-label]');
        return $promotion_label_element !== null ? $promotion_label_element->get_text() : null;
    }
    public function is_product_on_page_with_name(string $product_name): bool
    {
        return $this->has_element('product_name', ['%productName%' => $product_name]);
    }
    public function has_products_in_order(array $product_names): bool
    {
        $products_list = $this->get_element('products');
        $products = $products_list->find_all('css', '[data-test-product-content] > [data-test-product-name]');
        foreach ($product_names as $key => $value) {
            if ($products[$key]->get_text() !== $value) {
                return false;
            }
        }
        return true;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['clear' => '[data-test-clear]', 'product_name' => '[data-test-product-name="%productName%"]', 'product' => '[data-test-product=%productCode%]', 'products' => '[data-test-products]', 'search_button' => '[data-test-search]', 'flash_message' => '[data-test-sylius-flash-message]']);
    }
}