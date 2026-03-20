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

use Sylius\Bundle\Admin_Bundle\Form\Event_Subscriber\Add_User_Form_Subscriber;
use Sylius\Bundle\Customer_Bundle\Form\Type\Customer_Type as BaseCustomerType;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Validator\Constraints\Valid;
final class Customer_Type extends Abstract_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('firstName', Text_Type::class, ['label' => 'sylius.form.customer.first_name', 'required' => false])->add('lastName', Text_Type::class, ['label' => 'sylius.form.customer.last_name', 'required' => false])->add('user', Shop_User_Type::class, ['constraints' => [new Valid()], 'required' => false]);
        $builder->add_event_subscriber(new Add_User_Form_Subscriber());
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_customer';
    }
    public function get_parent(): string
    {
        return Base_Customer_Type::class;
    }
}