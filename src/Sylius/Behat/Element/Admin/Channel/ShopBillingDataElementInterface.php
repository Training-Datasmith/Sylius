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

interface Shop_Billing_Data_Element_Interface
{
    public function specify_company(string $company): void;
    public function specify_tax_id(string $tax_id): void;
    public function specify_billing_address(string $street, string $postcode, string $city, string $country_code): void;
    public function has_company(string $company): bool;
    public function has_tax_id(string $tax_id): bool;
    public function has_billing_address(string $street, string $postcode, string $city, string $country_code): bool;
}