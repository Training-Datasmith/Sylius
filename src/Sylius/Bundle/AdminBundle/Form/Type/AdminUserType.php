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

use Sylius\Bundle\Core_Bundle\Form\Type\User\Admin_User_Type as BaseAdminUserType;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Password_Type;
use Symfony\Component\Form\Form_Builder_Interface;
final class Admin_User_Type extends Abstract_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('plainPassword', Password_Type::class, ['label' => 'sylius.form.user.password.label', 'always_empty' => false]);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_admin_user';
    }
    public function get_parent(): string
    {
        return Base_Admin_User_Type::class;
    }
}