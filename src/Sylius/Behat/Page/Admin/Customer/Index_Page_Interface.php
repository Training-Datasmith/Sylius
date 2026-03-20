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
namespace Sylius\Behat\Page\Admin\Customer;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
use Sylius\Component\Customer\Model\Customer_Interface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function is_customer_enabled(Customer_Interface $customer): bool;
    public function is_customer_verified(Customer_Interface $customer): bool;
    public function set_filter_group(string $group_name): void;
}