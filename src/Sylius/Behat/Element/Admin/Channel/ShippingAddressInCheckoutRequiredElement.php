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
namespace Sylius\Behat\Element\Admin\Channel;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Element\Sylius_Element;
use Webmozart\Assert\Assert;
class Shipping_Address_In_Checkout_Required_Element extends Sylius_Element implements Shipping_Address_In_Checkout_Required_Element_Interface
{
    protected const ADDRESS_TYPE_BILLING = 'billing';
    protected const ADDRESS_TYPE_SHIPPING = 'shipping';
    public function require_shipping_address_in_checkout(): void
    {
        $this->require_address_type_in_checkout(self::ADDRESS_TYPE_SHIPPING);
    }
    public function require_billing_address_in_checkout(): void
    {
        $this->require_address_type_in_checkout(self::ADDRESS_TYPE_BILLING);
    }
    public function require_address_type_in_checkout(string $type): void
    {
        $this->get_choice_for_address_type($type)->click();
    }
    public function is_shipping_address_in_checkout_required(): bool
    {
        return self::ADDRESS_TYPE_SHIPPING === $this->get_required_address_type_in_checkout();
    }
    public function get_required_address_type_in_checkout(): string
    {
        foreach ($this->get_choices() as $type => $choice) {
            if ($choice->is_checked()) {
                return $type;
            }
        }
        throw new \InvalidArgumentException('No address type selected.');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['shipping_address_in_checkout_required' => '[data-test-shipping-address-in-checkout-required]']);
    }
    protected function get_choice_for_address_type(string $type): Node_Element
    {
        $choices = $this->get_choices();
        Assert::key_exists($choices, $type);
        return $choices[$type];
    }
    /** @return array<string, NodeElement> */
    protected function get_choices(): array
    {
        $element = $this->get_element('shipping_address_in_checkout_required');
        $labels_elements = $element->find_all('css', 'label');
        $choices = [];
        foreach ($labels_elements as $label_element) {
            $label = strtolower((string) $label_element->get_text());
            foreach ([self::ADDRESS_TYPE_BILLING, self::ADDRESS_TYPE_SHIPPING] as $type) {
                if (str_contains($label, $type)) {
                    $choices[$type] = $element->find_by_id($label_element->get_attribute('for'));
                    continue 2;
                }
            }
        }
        return $choices;
    }
}