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
namespace Sylius\Behat\Page\Shop\Checkout;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Router_Interface;
class Complete_Page extends Sylius_Page implements Complete_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected Table_Accessor_Interface $table_accessor)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_checkout_complete';
    }
    public function has_item_with_product_and_quantity(string $product_name, string $quantity): bool
    {
        $table = $this->get_element('items_table');
        try {
            $this->table_accessor->get_row_with_fields($table, ['item' => $product_name, 'qty' => $quantity]);
        } catch (\InvalidArgumentException) {
            return false;
        }
        return true;
    }
    public function has_shipping_address(Address_Interface $address): bool
    {
        $shipping_address = $this->get_element('shipping_address')->get_text();
        return $this->is_address_valid($shipping_address, $address);
    }
    public function has_billing_address(Address_Interface $address): bool
    {
        $billing_address = $this->get_element('billing_address')->get_text();
        return $this->is_address_valid($billing_address, $address);
    }
    public function has_shipping_method(Shipping_Method_Interface $shipping_method): bool
    {
        if (!$this->has_element('shipping_method')) {
            return false;
        }
        return str_contains($this->get_element('shipping_method')->get_text(), (string) $shipping_method->get_name());
    }
    public function get_payment_method_name(): string
    {
        return $this->get_element('payment_method')->get_text();
    }
    public function has_payment_method(): bool
    {
        return $this->has_element('payment_method');
    }
    public function get_product_unit_price(Product_Interface $product): int
    {
        return $this->get_price_from_string($this->get_element('product_unit_price', ['%name%' => $product->get_name()])->get_text());
    }
    public function has_product_discounted_unit_price_by(Product_Interface $product, int $amount): bool
    {
        $price_without_discount = $this->get_price_from_string($this->get_element('product_old_price', ['%name%' => $product->get_name()])->get_text());
        $price_with_discount = $this->get_price_from_string($this->get_element('product_unit_price', ['%name%' => $product->get_name()])->get_text());
        $discount = $price_without_discount - $price_with_discount;
        return $discount === $amount;
    }
    public function has_order_total(int $total): bool
    {
        if (!$this->has_element('order_total')) {
            return false;
        }
        return $this->get_total_from_string($this->get_element('order_total')->get_text()) === $total;
    }
    public function get_base_currency_order_total(): string
    {
        return (string) $this->get_base_total_from_string($this->get_element('base_order_total')->get_text());
    }
    public function add_notes(string $notes): void
    {
        $this->get_element('extra_notes')->set_value($notes);
    }
    public function has_promotion_total(string $promotion_total): bool
    {
        return str_contains($this->get_element('promotion_total')->get_text(), $promotion_total);
    }
    public function has_promotion(string $promotion_name): bool
    {
        return false !== stripos($this->get_element('promotion_discounts')->get_text(), $promotion_name);
    }
    public function has_shipping_promotion(string $promotion_name): bool
    {
        /** @var NodeElement $shippingPromotions */
        $shipping_promotions = $this->get_element('promotions_shipping_details');
        return str_contains((string) $shipping_promotions->get_text(), $promotion_name);
    }
    public function get_tax_total(): string
    {
        return $this->get_element('tax_total')->get_text();
    }
    public function get_shipping_total(): string
    {
        return $this->get_element('shipping_total')->get_text();
    }
    public function has_shipping_total(): bool
    {
        return $this->has_element('shipping_total');
    }
    public function has_product_unit_price(Product_Interface $product, string $price): bool
    {
        return $this->get_price_from_string($this->get_element('product_unit_price', ['%name%' => $product->get_name()])->get_text()) === $this->get_price_from_string($price);
    }
    public function has_product_out_of_stock_validation_message(Product_Interface $product): bool
    {
        $message = sprintf('%s does not have sufficient stock.', $product->get_name());
        return $this->get_element('validation_errors')->get_text() === $message;
    }
    public function get_validation_errors(): string
    {
        return $this->get_element('validation_errors')->get_text();
    }
    public function has_locale(string $locale_name): bool
    {
        return str_contains($this->get_element('locale')->get_text(), $locale_name);
    }
    public function has_currency(string $currency_code): bool
    {
        return str_contains($this->get_element('currency')->get_text(), $currency_code);
    }
    public function confirm_order(): void
    {
        $this->get_element('confirm_button')->press();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function change_address(): void
    {
        $this->get_element('addressing_step_label')->click();
    }
    public function change_shipping_method(): void
    {
        $this->get_element('shipping_step_label')->click();
    }
    public function change_payment_method(): void
    {
        $this->get_element('payment_step_label')->click();
    }
    public function has_shipping_province_name(string $province_name): bool
    {
        $shipping_address_text = $this->get_element('shipping_address')->get_text();
        return false !== stripos($shipping_address_text, $province_name);
    }
    public function has_billing_province_name(string $province_name): bool
    {
        $billing_address_text = $this->get_element('billing_address')->get_text();
        return false !== stripos($billing_address_text, $province_name);
    }
    public function has_shipping_promotion_with_discount(string $promotion_name, string $discount): bool
    {
        $promotion_with_discount = sprintf('%s: %s', $promotion_name, $discount);
        /** @var NodeElement $shippingPromotions */
        $shipping_promotions = $this->get_element('promotions_shipping_details');
        return str_contains((string) $shipping_promotions->get_text(), $promotion_with_discount);
    }
    public function has_order_promotion(string $promotion_name): bool
    {
        /** @var NodeElement $shippingPromotions */
        $shipping_promotions = $this->get_element('order_promotions_details');
        return str_contains((string) $shipping_promotions->get_text(), $promotion_name);
    }
    public function try_to_open(array $url_parameters = []): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $start = microtime(true);
            $end = $start + 15;
            do {
                parent::try_to_open($url_parameters);
                sleep(3);
            } while (!$this->is_open() && microtime(true) < $end);
            return;
        }
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['addressing_step_label' => '[data-test-step-address]', 'base_order_total' => '[data-test-summary-order-total]', 'billing_address' => '[data-test-billing-address]', 'confirm_button' => '[data-test-button="confirmation-button"]', 'currency' => '[data-test-order-currency-code]', 'extra_notes' => '[data-test-extra-notes]', 'items_table' => '[data-test-order-table]', 'locale' => '[data-test-order-locale-name]', 'order_promotions_details' => '[data-test-order-promotions-details]', 'order_total' => '[data-test-order-total]', 'payment_method' => '[data-test-payment-method]', 'payment_step_label' => '[data-test-step-payment]', 'product_old_price' => '[data-test-product-old-price="%name%"]', 'product_row' => '[data-test-product-row="%name%"]', 'product_unit_price' => '[data-test-product-unit-price="%name%"]', 'promotion_discounts' => '[data-test-promotion-discounts]', 'promotion_shipping_discounts' => '[data-test-promotion-shipping-discounts]', 'promotion_total' => '[data-test-promotion-total]', 'promotions_shipping_details' => '[data-test-shipping-promotion-details]', 'shipping_address' => '[data-test-shipping-address]', 'shipping_method' => '[data-test-shipping-method]', 'shipping_step_label' => '[data-test-step-shipping]', 'shipping_total' => '[data-test-shipping-total]', 'tax_total' => '[data-test-tax-total]', 'validation_errors' => '[data-test-validation-error]']);
    }
    protected function is_address_valid(string $displayed_address, Address_Interface $address): bool
    {
        return $this->has_address_part($displayed_address, $address->get_company(), true) && $this->has_address_part($displayed_address, $address->get_first_name()) && $this->has_address_part($displayed_address, $address->get_last_name()) && $this->has_address_part($displayed_address, $address->get_phone_number(), true) && $this->has_address_part($displayed_address, $address->get_street()) && $this->has_address_part($displayed_address, $address->get_city()) && $this->has_address_part($displayed_address, $address->get_province_code(), true) && $this->has_address_part($displayed_address, $this->get_country_name($address->get_country_code())) && $this->has_address_part($displayed_address, $address->get_postcode());
    }
    protected function has_address_part(string $address, ?string $address_part, bool $optional = false): bool
    {
        if ($optional && null === $address_part) {
            return true;
        }
        return str_contains($address, (string) $address_part);
    }
    protected function get_country_name(string $country_code): string
    {
        return strtoupper(Countries::get_name($country_code, 'en'));
    }
    protected function get_price_from_string(string $price): int
    {
        return (int) round((float) str_replace(['€', '£', '$'], '', $price) * 100, 2);
    }
    protected function get_total_from_string(string $total): int
    {
        $total = str_replace('Total:', '', $total);
        return $this->get_price_from_string($total);
    }
    protected function get_base_total_from_string(string $total): int
    {
        $total = str_replace('Total in base currency:', '', $total);
        return $this->get_price_from_string($total);
    }
}