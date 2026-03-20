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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Channel\Shop_Billing_Data_Element_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Channels_Billing_Data_Context implements Context
{
    public function __construct(private Shop_Billing_Data_Element_Interface $shop_billing_data_element)
    {
    }
    #[When('I specify shop billing data for this channel as :company, :street, :postcode, :city, :taxId tax ID and :country country')]
    public function i_specify_new_shop_billing_data_for_channel_as(string $company, string $street, string $postcode, string $city, string $tax_id, Country_Interface $country): void
    {
        $this->shop_billing_data_element->specify_company($company);
        $this->shop_billing_data_element->specify_tax_id($tax_id);
        $this->shop_billing_data_element->specify_billing_address($street, $postcode, $city, $country->get_code());
    }
    #[When('I specify company as :company')]
    public function specify_company_as(string $company): void
    {
        $this->shop_billing_data_element->specify_company($company);
    }
    #[When('I specify tax ID as :taxId')]
    public function specify_tax_id_as(string $tax_id): void
    {
        $this->shop_billing_data_element->specify_tax_id($tax_id);
    }
    #[When('I specify shop billing address as :street, :postcode :city, :country')]
    public function specify_shop_billing_address_as(string $street, string $postcode, string $city, Country_Interface $country): void
    {
        $this->shop_billing_data_element->specify_billing_address($street, $postcode, $city, $country->get_code());
    }
    #[Then('this channel company should be :company')]
    public function this_channel_company_should_be(string $company): void
    {
        Assert::true($this->shop_billing_data_element->has_company($company));
    }
    #[Then('this channel tax ID should be :taxId')]
    public function this_channe_tax_id_should_be(string $tax_id): void
    {
        Assert::true($this->shop_billing_data_element->has_tax_id($tax_id));
    }
    #[Then('this channel shop billing address should be :street, :postcode :city and :country country')]
    #[Then('this channel shop billing address should be :street, :postcode :city, :country')]
    public function this_channel_shop_billing_address_should_be(string $street, string $postcode, string $city, Country_Interface $country): void
    {
        Assert::true($this->shop_billing_data_element->has_billing_address($street, $postcode, $city, $country->get_code()));
    }
}