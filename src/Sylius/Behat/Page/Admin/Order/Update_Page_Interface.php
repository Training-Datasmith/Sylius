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

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
use Sylius\Component\Addressing\Model\Address_Interface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function specify_shipping_address(Address_Interface $address): void;
    public function specify_billing_address(Address_Interface $address): void;
    public function check_validation_message_for(string $element, string $message): bool;
    public function change_billing_country(string $country_code): void;
    public function change_shipping_country(string $country_code): void;
    /**
     * @return array<string>
     */
    public function get_available_provinces_for_billing_address(): array;
    /**
     * @return array<string>
     */
    public function get_available_provinces_for_shipping_address(): array;
}