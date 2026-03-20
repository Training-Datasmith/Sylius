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
namespace Sylius\Bundle\Admin_Bundle\Form;

use Sylius\Bundle\Admin_Bundle\Form\Model\Password_Reset_Request;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Email_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Request_Password_Reset_Type extends Abstract_Type
{
    /** @param string[] $validationGroups */
    public function __construct(private readonly array $validation_groups = [])
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('email', Email_Type::class, ['label' => 'sylius.ui.email', 'required' => true]);
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['data_class' => Password_Reset_Request::class, 'validation_groups' => $this->validation_groups]);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_request_password_reset';
    }
}