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

use Sylius\Component\Core\Factory\Address_Factory_Interface as BaseAddressFactoryInterface;
use Sylius\Component\Core\Model\Address_Interface;
/** @extends BaseAddressFactoryInterface<AddressInterface> */
interface Address_Factory_Interface extends Base_Address_Factory_Interface
{
    public function create_default(): Address_Interface;
    public function create_default_with_country_code(string $country_code): Address_Interface;
    public function create_default_with_province_name(string $province_name): Address_Interface;
    public function create_default_with_first_and_last_name(string $first_name, string $last_name): Address_Interface;
}