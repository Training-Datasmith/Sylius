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
namespace Sylius\Behat\Page\Admin\Order;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
use Sylius\Component\Addressing\Model\Address_Interface;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    public const TYPE_BILLING = 'billing';
    public const TYPE_SHIPPING = 'shipping';
    public function specify_billing_address(Address_Interface $address): void
    {
        $this->specify_address($address, self::TYPE_BILLING);
    }
    public function specify_shipping_address(Address_Interface $address): void
    {
        $this->specify_address($address, self::TYPE_SHIPPING);
    }
    protected function specify_address(Address_Interface $address, $address_type): void
    {
        $this->specify_element_value($address_type . '_first_name', $address->get_first_name());
        $this->specify_element_value($address_type . '_last_name', $address->get_last_name());
        $this->specify_element_value($address_type . '_street', $address->get_street());
        $this->specify_element_value($address_type . '_city', $address->get_city());
        $this->specify_element_value($address_type . '_postcode', $address->get_postcode());
        $this->choose_country($address->get_country_code(), $address_type);
    }
    /**
     * @throws ElementNotFoundException
     */
    public function check_validation_message_for(string $element, string $message): bool
    {
        $found_element = $this->get_field_element($element);
        if (null === $found_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        $validation_message = $found_element->find('css', '.invalid-feedback');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        return $message === $validation_message->get_text();
    }
    public function change_billing_country(string $country_code): void
    {
        $this->get_element('billing_country')->select_option($country_code);
        $this->wait_for_form_update();
    }
    public function change_shipping_country(string $country_code): void
    {
        $this->get_element('shipping_country')->select_option($country_code);
        $this->wait_for_form_update();
    }
    public function get_available_provinces_for_billing_address(): array
    {
        return $this->get_option_texts_for($this->get_element('billing_province_code'));
    }
    public function get_available_provinces_for_shipping_address(): array
    {
        return $this->get_option_texts_for($this->get_element('shipping_province_code'));
    }
    /**
     * @return array<string>
     */
    protected function get_option_texts_for(Node_Element $element): array
    {
        return array_map(fn($option) => $option->get_text(), $element->find_all('css', 'option'));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['billing_city' => '#sylius_admin_order_billingAddress_city', 'billing_country' => '#sylius_admin_order_billingAddress_countryCode', 'billing_first_name' => '#sylius_admin_order_billingAddress_firstName', 'billing_last_name' => '#sylius_admin_order_billingAddress_lastName', 'billing_postcode' => '#sylius_admin_order_billingAddress_postcode', 'billing_province_name' => '#sylius_admin_order_billingAddress_provinceName', 'billing_province_code' => '#sylius_admin_order_billingAddress_provinceCode', 'billing_street' => '#sylius_admin_order_billingAddress_street', 'live_form' => '[data-live-name-value="sylius_admin:order:form"]', 'shipping_city' => '#sylius_admin_order_shippingAddress_city', 'shipping_country' => '#sylius_admin_order_shippingAddress_countryCode', 'shipping_first_name' => '#sylius_admin_order_shippingAddress_firstName', 'shipping_last_name' => '#sylius_admin_order_shippingAddress_lastName', 'shipping_postcode' => '#sylius_admin_order_shippingAddress_postcode', 'shipping_province_name' => '#sylius_admin_order_shippingAddress_provinceName', 'shipping_province_code' => '#sylius_admin_order_shippingAddress_provinceCode', 'shipping_street' => '#sylius_admin_order_shippingAddress_street']);
    }
    /**
     * @throws ElementNotFoundException
     */
    protected function specify_element_value(string $element_name, ?string $value): void
    {
        $this->get_element($element_name)->set_value($value);
    }
    /**
     * @throws ElementNotFoundException
     */
    protected function choose_country(?string $country, string $address_type): void
    {
        $this->get_element($address_type . '_country')->select_option($country ?? 'Select');
    }
    /**
     * @throws ElementNotFoundException
     */
    protected function get_field_element(string $element): Node_Element
    {
        $element = $this->get_element($element);
        while (null !== $element && !$element->has_class('field')) {
            $element = $element->get_parent();
        }
        return $element;
    }
    protected function wait_for_form_update(): void
    {
        $this->get_element('live_form')->wait_for('5', fn(Node_Element $element): bool => !$element->has_attribute('busy'));
    }
}