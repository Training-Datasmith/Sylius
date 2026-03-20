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
namespace Sylius\Bundle\Admin_Bundle\Form\Type;

use Sylius\Bundle\Customer_Bundle\Form\Type\Customer_Group_Type as BaseCustomerGroupType;
use Symfony\Component\Form\Abstract_Type;
final class Customer_Group_Type extends Abstract_Type
{
    public function get_block_prefix(): string
    {
        return 'sylius_admin_customer_group';
    }
    public function get_parent(): string
    {
        return Base_Customer_Group_Type::class;
    }
}