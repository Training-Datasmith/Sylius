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
namespace Sylius\Behat\Page\Shop\Cart;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Shop\Page as ShopPage;
use Sylius\Component\Core\Model\Product_Interface;
class Summary_Page extends Shop_Page implements Summary_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_cart_summary';
    }
    public function get_grand_total(): string
    {
        return $this->get_element('grand_total')->get_text();
    }
    public function get_base_grand_total(): string
    {
        return $this->get_element('base_grand_total')->get_text();
    }
    public function get_included_tax_total(): string
    {
        return $this->get_element('tax_included')->get_text();
    }
    public function get_excluded_tax_total(): string
    {
        return $this->get_element('tax_excluded')->get_text();
    }
    public function are_taxes_charged(): bool
    {
        try {
            $this->get_element('no_taxes');
        } catch (Element_Not_Found_Exception) {
            return true;
        }
        return false;
    }
    public function get_shipping_total(): string
    {
        return $this->get_element('shipping_total')->get_text();
    }
    public function has_shipping_total(): bool
    {
        return $this->has_element('shipping_total');
    }
    public function get_promotion_total(): string
    {
        return $this->get_element('promotion_total')->get_text();
    }
    public function get_items_total(): string
    {
        return $this->get_element('items_total')->get_text();
    }
    public function get_item_total(string $product_name): string
    {
        $item_total_element = $this->get_element('product_total', ['%name%' => $product_name]);
        return $item_total_element->get_text();
    }
    public function get_item_unit_regular_price(string $product_name): string
    {
        return $this->get_element('item_unit_regular_price', ['%name%' => $product_name])->get_text();
    }
    public function get_item_unit_price(string $product_name): string
    {
        return $this->get_element('item_unit_price', ['%name%' => $product_name])->get_text();
    }
    public function has_original_price(string $product_name): bool
    {
        return $this->has_element('item_unit_regular_price', ['%name%' => $product_name]);
    }
    public function get_item_image(int $item_number): string
    {
        return $this->get_element('item_image', ['%number%' => $item_number - 1])->get_attribute('src');
    }
    public function is_item_discounted(string $product_name): bool
    {
        return $this->has_element('item_unit_regular_price', ['%name%' => $product_name]);
    }
    public function remove_product(string $product_name): void
    {
        $this->get_element('remove_item', ['%name%' => $product_name])->press();
        $this->wait_for_components_update();
    }
    public function apply_coupon(string $coupon_code): void
    {
        $this->get_element('coupon_field')->set_value($coupon_code);
        $this->get_element('apply_coupon_button')->press();
        $this->wait_for_components_update();
    }
    public function remove_coupon(): void
    {
        $this->get_element('remove_coupon')->press();
        $this->wait_for_components_update();
    }
    public function change_quantity(string $product_name, string $quantity): void
    {
        $this->get_element('item_quantity', ['%name%' => $product_name])->set_value($quantity);
        $this->wait_for_components_update();
    }
    public function count_order_items(): int
    {
        return count($this->get_element('cart_items')->find_all('css', '[data-test-cart-item]'));
    }
    public function has_item_named(string $name): bool
    {
        return $this->has_element('cart_item', ['%name%' => $name]);
    }
    public function has_item_with_variant_named(string $variant_name): bool
    {
        $cart_items = $this->get_element('cart_items');
        foreach ($cart_items->find_all('css', '[data-test-product-variant-name]') as $element_variant_name) {
            if ($variant_name === $element_variant_name->get_text()) {
                return true;
            }
        }
        return false;
    }
    public function get_item_option_value(string $product_name, string $option_name): string
    {
        return $this->get_element('item_product_option_value', ['%name%' => $product_name, '%option_name%' => $option_name])->get_text();
    }
    public function has_item_with_code(string $code): bool
    {
        return $this->has_element('item_product_variant_code', ['%code%' => $code]);
    }
    public function has_item_with_insufficient_stock(string $product_name): bool
    {
        $product = $this->get_element('product_row', ['%name%' => $product_name]);
        return str_contains($product->get_text(), 'Insufficient stock');
    }
    public function cart_is_empty(): bool
    {
        return str_contains($this->get_element('flash_message_info')->get_text(), 'Your cart is empty');
    }
    public function get_quantity(string $product_name): int
    {
        return (int) $this->get_element('item_quantity', ['%name%' => $product_name])->get_value();
    }
    public function get_cart_total(): string
    {
        $cart_total_text = $this->get_element('cart_total')->get_text();
        if (str_contains($cart_total_text, ',')) {
            return strstr($cart_total_text, ',', true);
        }
        return trim($cart_total_text);
    }
    public function clear_cart(): void
    {
        $this->get_element('clear_cart')->click();
        $this->wait_for_components_update();
    }
    public function checkout(): void
    {
        $this->get_element('checkout_button')->click();
    }
    public function wait_for_redirect(int $timeout): void
    {
        $this->get_document()->wait_for($timeout, fn() => $this->is_open());
    }
    public function has_product_out_of_stock_validation_message(Product_Interface $product): bool
    {
        $message = sprintf('%s does not have sufficient stock.', $product->get_name());
        return $this->has_element('validation_errors') && $this->get_element('validation_errors')->get_text() === $message;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['apply_coupon_button' => '[data-test-apply-coupon-button]', 'base_grand_total' => '[data-test-cart-base-grand-total]', 'cart_items' => '[data-test-cart-items]', 'cart_item' => '[data-test-cart-items] [data-test-cart-item-product-row="%name%"]', 'cart_total' => '[data-test-cart-total]', 'checkout_button' => '[data-test-cart-checkout-button]', 'clear_cart' => '[data-test-clear-cart]', 'coupon_field' => '[data-test-cart-promotion-coupon-input]', 'flash_message_info' => '[data-test-sylius-flash-message="alert-info"]', 'form' => '[data-live-name-value="sylius_shop:cart:form"]', 'summary_component' => '[data-live-name-value="sylius_shop:cart:summary"]', 'grand_total' => '[data-test-cart-grand-total]', 'item_image' => '[data-test-cart-items] [data-test-cart-item="%number%"] [data-test-cart-item-product] [data-test-main-image]', 'item_product_option_value' => '[data-test-cart-items] [data-test-cart-item-product-row="%name%"] [data-test-cart-item-product] [data-test-option-name="%option_name%"] [data-test-option-value]', 'item_product_variant_code' => '[data-test-cart-items] [data-test-cart-item-product] [data-test-product-variant-code="%code%"]', 'item_quantity' => '[data-test-cart-items] [data-test-cart-item-product-row="%name%"] [data-test-cart-item-quantity]', 'item_unit_price' => '[data-test-cart-items] [data-test-cart-item-product-row="%name%"] [data-test-cart-item-unit-price]', 'item_unit_regular_price' => '[data-test-cart-items] [data-test-cart-item-product-row="%name%"] [data-test-cart-item-unit-regular-price]', 'items_total' => '[data-test-cart-items-total]', 'no_taxes' => '[data-test-cart-no-tax]', 'product_row' => '[data-test-cart-item-product-row="%name%"]', 'product_total' => '[data-test-cart-item-product-row="%name%"] [data-test-cart-product-subtotal]', 'promotion_coupon' => '[data-test-cart-promotion-coupon]', 'promotion_total' => '[data-test-cart-promotion-total]', 'remove_coupon' => '[data-test-cart-promotion-remove-coupon]', 'remove_item' => '[data-test-cart-items] [data-test-cart-item-product-row="%name%"] [data-test-remove-cart-item]', 'shipping_total' => '[data-test-cart-shipping-total]', 'tax_excluded' => '[data-test-cart-tax-excluded]', 'tax_included' => '[data-test-cart-tax-included]', 'validation_errors' => '[data-test-validation-error]']);
    }
    protected function wait_for_components_update(): void
    {
        $this->wait_for_element_update('form');
        try {
            $this->wait_for_element_update('summary_component');
        } catch (Element_Not_Found_Exception) {
            return;
        }
    }
}