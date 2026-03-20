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
namespace Sylius\Behat\Service\Factory;

use Sylius\Component\Core\Factory\Address_Factory as BaseAddressFactory;
use Sylius\Component\Core\Model\Address_Interface;
final class Address_Factory extends Base_Address_Factory implements Address_Factory_Interface
{
    public function __construct(private readonly Base_Address_Factory $decorated_address_factory)
    {
        parent::__construct($decorated_address_factory);
    }
    public function create_default(): Address_Interface
    {
        $address = $this->decorated_address_factory->create_new();
        $address->set_city('New York');
        $address->set_street('Wall Street');
        $address->set_postcode('00-001');
        $address->set_country_code('US');
        $address->set_province_name('Arkansas');
        $address->set_first_name('Richy');
        $address->set_last_name('Rich');
        return $address;
    }
    public function create_default_with_country_code(string $country_code): Address_Interface
    {
        $address = $this->decorated_address_factory->create_new();
        $address->set_city('New York');
        $address->set_street('Wall Street');
        $address->set_postcode('00-001');
        $address->set_country_code($country_code);
        $address->set_first_name('Richy');
        $address->set_last_name('Rich');
        return $address;
    }
    public function create_default_with_province_name(string $province_name): Address_Interface
    {
        $address = $this->create_default();
        $address->set_province_name($province_name);
        return $address;
    }
    public function create_default_with_first_and_last_name(string $first_name, string $last_name): Address_Interface
    {
        $address = $this->create_default();
        $address->set_first_name($first_name);
        $address->set_last_name($last_name);
        return $address;
    }
}