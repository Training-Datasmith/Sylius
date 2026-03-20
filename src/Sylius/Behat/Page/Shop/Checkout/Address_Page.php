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
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Exception\Unsupported_Driver_Action_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Page\Shop\Page as ShopPage;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Factory\Address_Factory_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Symfony\Component\Routing\Router_Interface;
use Webmozart\Assert\Assert;
class Address_Page extends Shop_Page implements Address_Page_Interface
{
    use Secure_Password_Trait;
    public const TYPE_BILLING = 'billing';
    public const TYPE_SHIPPING = 'shipping';
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected Address_Factory_Interface $address_factory, protected Shared_Storage_Interface $shared_storage)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_checkout_address';
    }
    public function choose_different_shipping_address(): void
    {
        $this->choose_different_address('shipping');
    }
    public function choose_different_billing_address(): void
    {
        $this->choose_different_address('billing');
    }
    public function is_different_shipping_address_checked(): bool
    {
        return $this->get_element('different_shipping_address')->is_checked();
    }
    public function is_shipping_address_visible(): bool
    {
        try {
            return $this->get_element('shipping_address')->is_visible();
        } catch (Unsupported_Driver_Action_Exception) {
            // it's visible by default and is being hidden with JS
            return true;
        }
    }
    public function check_invalid_credentials_validation(): bool
    {
        $validation_element = $this->get_document()->wait_for(3, fn(): Node_Element => $this->get_element('login_validation_error'));
        return $validation_element->get_text() === 'Invalid credentials.';
    }
    public function check_validation_message_for(string $element, string $message): bool
    {
        try {
            $found_element = $this->get_field_element($element);
        } catch (Element_Not_Found_Exception) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        $validation_message = $found_element->find('css', '[data-test-validation-error]');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        return $message === $validation_message->get_text();
    }
    public function check_form_validation_message(string $message): bool
    {
        $form_element = $this->get_element('form');
        if (null === $form_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Form');
        }
        $validation_message = $form_element->find_all('css', '[data-test-validation-error]');
        foreach ($validation_message as $validation_message) {
            if ($validation_message->get_text() === $message) {
                return true;
            }
        }
        return false;
    }
    public function specify_shipping_address(Address_Interface $shipping_address): void
    {
        $this->specify_address($shipping_address, self::TYPE_SHIPPING);
    }
    public function select_shipping_address_province(string $province): void
    {
        $this->wait_for_element_update('form');
        $this->get_element('shipping_country_province')->select_option($province);
    }
    public function specify_billing_address(Address_Interface $billing_address): void
    {
        $this->specify_address($billing_address, self::TYPE_BILLING);
    }
    public function select_billing_address_province(string $province): void
    {
        $this->wait_for_element_update('form');
        $this->get_element('billing_country_province')->select_option($province);
    }
    public function specify_email(?string $email): void
    {
        $this->get_element('customer_email')->set_value($email);
    }
    public function specify_billing_address_full_name(string $full_name): void
    {
        $names = explode(' ', $full_name);
        $this->get_element('billing_first_name')->set_value($names[0]);
        $this->get_element('billing_last_name')->set_value($names[1]);
    }
    public function can_sign_in(): bool
    {
        $this->wait_for_element_update('form');
        return $this->has_element('login_button');
    }
    public function sign_in(): void
    {
        $this->wait_for_element_update('form');
        try {
            $this->get_element('login_button')->press();
        } catch (Element_Not_Found_Exception) {
            $this->get_element('login_button')->click();
        }
        $this->wait_for_login_action();
    }
    public function specify_password(string $password): void
    {
        $this->wait_for_element_update('form');
        $this->get_element('login_password')->set_value($this->retrieve_secure_password($password));
    }
    public function get_item_subtotal(string $item_name): string
    {
        $item_slug = strtolower(str_replace('\"', '', str_replace(' ', '-', $item_name)));
        $subtotal_table = $this->get_element('checkout_subtotal');
        return $subtotal_table->find('css', sprintf('[data-test-item-subtotal="%s"]', $item_slug))->get_text();
    }
    public function get_shipping_address_country(): string
    {
        return $this->get_element('shipping_country')->find('css', 'option:selected')->get_text();
    }
    public function get_billing_address_country(): string
    {
        return $this->get_element('billing_country')->find('css', 'option:selected')->get_text();
    }
    public function next_step(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $this->blur();
            Driver_Helper::wait_for_page_to_load($this->get_session());
        }
        $this->get_element('next_step')->press();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function back_to_store(): void
    {
        $this->get_document()->click_link('Back to store');
    }
    public function specify_billing_address_province(string $province_name): void
    {
        $this->wait_for_element_update('form');
        $this->get_element('billing_province')->set_value($province_name);
    }
    public function specify_shipping_address_province(string $province_name): void
    {
        $this->wait_for_element_update('form');
        $this->get_element('shipping_province')->set_value($province_name);
    }
    public function has_shipping_address_input(): bool
    {
        $this->wait_for_element_update('form');
        return $this->has_element('shipping_province');
    }
    public function has_email_input(): bool
    {
        return $this->has_element('customer_email');
    }
    public function has_billing_address_input(): bool
    {
        $this->wait_for_element_update('form');
        return $this->has_element('billing_province');
    }
    public function select_shipping_address_from_address_book(Address_Interface $address): void
    {
        $this->get_element('shipping_address_book')->select_option($address->get_id());
        $this->wait_for_element_update('form');
    }
    public function select_billing_address_from_address_book(Address_Interface $address): void
    {
        $this->get_element('billing_address_book')->select_option($address->get_id());
        $this->wait_for_element_update('form');
    }
    public function get_pre_filled_shipping_address(): Address_Interface
    {
        return $this->get_pre_filled_address(self::TYPE_SHIPPING);
    }
    public function get_pre_filled_billing_address(): Address_Interface
    {
        return $this->get_pre_filled_address(self::TYPE_BILLING);
    }
    public function get_available_shipping_countries(): array
    {
        return $this->get_options_from_select($this->get_element('shipping_country'));
    }
    public function get_available_billing_countries(): array
    {
        return $this->get_options_from_select($this->get_element('billing_country'));
    }
    public function wait_for_form_to_stop_loading(): void
    {
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['billing_address_book' => '[data-test-billing-address] [data-test-address-book]', 'billing_city' => '[data-test-billing-address] [data-test-city]', 'billing_country' => '[data-test-billing-address] [data-test-country]', 'billing_country_province' => '[data-test-billing-address] [data-test-province-code]', 'billing_first_name' => '[data-test-billing-address] [data-test-first-name]', 'billing_last_name' => '[data-test-billing-address] [data-test-last-name]', 'billing_postcode' => '[data-test-billing-address] [data-test-postcode]', 'billing_province' => '[data-test-billing-address] [data-test-province-name]', 'billing_street' => '[data-test-billing-address] [data-test-street]', 'checkout_subtotal' => '[data-test-checkout-subtotal]', 'customer_email' => '[data-test-login-email]', 'different_billing_address' => '[data-test-different-billing-address]', 'different_shipping_address' => '[data-test-different-shipping-address]', 'form' => '[data-live-name-value="sylius_shop:checkout:address:form"]', 'login_button' => '[data-test-login-button]', 'login_password' => '[data-test-password-input]', 'login_validation_error' => '[data-test-login-validation-error]', 'next_step' => '[data-test-next-step]', 'shipping_address' => '[data-test-shipping-address]', 'shipping_address_book' => '[data-test-shipping-address] [data-test-address-book]', 'shipping_city' => '[data-test-shipping-address] [data-test-city]', 'shipping_country' => '[data-test-shipping-address] [data-test-country]', 'shipping_country_province' => '[data-test-shipping-address] [data-test-province-code]', 'shipping_first_name' => '[data-test-shipping-address] [data-test-first-name]', 'shipping_last_name' => '[data-test-shipping-address] [data-test-last-name]', 'shipping_postcode' => '[data-test-shipping-address] [data-test-postcode]', 'shipping_province' => '[data-test-shipping-address] [data-test-province-name]', 'shipping_street' => '[data-test-shipping-address] [data-test-street]']);
    }
    /** @return string[] */
    protected function get_options_from_select(Node_Element $element): array
    {
        return array_map(
            /** @return string[] */
            static fn(Node_Element $element): string => $element->get_text(),
            $element->find_all('css', 'option[value!=""]')
        );
    }
    protected function get_pre_filled_address(string $type): Address_Interface
    {
        $this->assert_address_type($type);
        /** @var AddressInterface $address */
        $address = $this->address_factory->create_new();
        $address->set_first_name($this->get_element(sprintf('%s_first_name', $type))->get_value());
        $address->set_last_name($this->get_element(sprintf('%s_last_name', $type))->get_value());
        $address->set_street($this->get_element(sprintf('%s_street', $type))->get_value());
        $address->set_country_code($this->get_element(sprintf('%s_country', $type))->get_value());
        $address->set_city($this->get_element(sprintf('%s_city', $type))->get_value());
        $address->set_postcode($this->get_element(sprintf('%s_postcode', $type))->get_value());
        $this->wait_for_element_update('form');
        try {
            $address->set_province_name($this->get_element(sprintf('%s_province', $type))->get_value());
        } catch (Element_Not_Found_Exception) {
            $address->set_province_code($this->get_element(sprintf('%s_country_province', $type))->get_value());
        }
        return $address;
    }
    protected function specify_address(Address_Interface $address, string $type): void
    {
        $this->assert_address_type($type);
        $this->get_element(sprintf('%s_first_name', $type))->set_value($address->get_first_name());
        $this->get_element(sprintf('%s_last_name', $type))->set_value($address->get_last_name());
        $this->get_element(sprintf('%s_street', $type))->set_value($address->get_street());
        $this->get_element(sprintf('%s_country', $type))->select_option($address->get_country_code() ?: 'Select');
        $this->get_element(sprintf('%s_city', $type))->set_value($address->get_city());
        $this->get_element(sprintf('%s_postcode', $type))->set_value($address->get_postcode());
        if (null !== $address->get_province_name()) {
            $this->wait_for_element_update('form');
            $this->get_element(sprintf('%s_province', $type))->set_value($address->get_province_name());
        }
        if (null !== $address->get_province_code()) {
            $this->wait_for_element_update('form');
            $this->get_element(sprintf('%s_country_province', $type))->select_option($address->get_province_code());
        }
    }
    protected function get_field_element(string $element, array $parameters = []): Node_Element
    {
        $element = $this->get_element($element, $parameters);
        while (null !== $element && !$element->has_class('field')) {
            $element = $element->get_parent();
        }
        return $element;
    }
    protected function wait_for_login_action(): bool
    {
        return $this->get_document()->wait_for(5, fn(): bool => !$this->has_element('login_password'));
    }
    protected function assert_address_type(string $type): void
    {
        $available_types = [self::TYPE_BILLING, self::TYPE_SHIPPING];
        Assert::one_of($type, $available_types, sprintf('There are only two available types %s, %s. %s given', self::TYPE_BILLING, self::TYPE_SHIPPING, $type));
    }
    protected function choose_different_address(string $type): void
    {
        $elem = $this->get_element(sprintf('different_%s_address', $type));
        $elem->click();
        $this->wait_for_element_update('form');
    }
}