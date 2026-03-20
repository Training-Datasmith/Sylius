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

use Sylius\Behat\Element\Sylius_Element;
class Shop_Billing_Data_Element extends Sylius_Element implements Shop_Billing_Data_Element_Interface
{
    public function specify_company(string $company): void
    {
        $this->get_element('company')->set_value($company);
    }
    public function specify_tax_id(string $tax_id): void
    {
        $this->get_element('tax_id')->set_value($tax_id);
    }
    public function specify_billing_address(string $street, string $postcode, string $city, string $country_code): void
    {
        $this->get_element('street')->set_value($street);
        $this->get_element('postcode')->set_value($postcode);
        $this->get_element('city')->set_value($city);
        $this->get_element('country_code')->set_value($country_code);
    }
    public function has_company(string $company): bool
    {
        return $company === $this->get_element('company')->get_value();
    }
    public function has_tax_id(string $tax_id): bool
    {
        return $tax_id === $this->get_element('tax_id')->get_value();
    }
    public function has_billing_address(string $street, string $postcode, string $city, string $country_code): bool
    {
        return $street === $this->get_element('street')->get_value() && $postcode === $this->get_element('postcode')->get_value() && $city === $this->get_element('city')->get_value() && $country_code === $this->get_element('country_code')->get_value();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['city' => '#sylius_admin_channel_shopBillingData_city', 'company' => '#sylius_admin_channel_shopBillingData_company', 'country_code' => '#sylius_admin_channel_shopBillingData_countryCode', 'postcode' => '#sylius_admin_channel_shopBillingData_postcode', 'street' => '#sylius_admin_channel_shopBillingData_street', 'tax_id' => '#sylius_admin_channel_shopBillingData_taxId']);
    }
}