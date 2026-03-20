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
namespace Sylius\Bundle\Addressing_Bundle\Form\Type;

use Sylius\Bundle\Resource_Bundle\Form\Type\Abstract_Resource_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Zone_Member_Type extends Abstract_Resource_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('code', $options['entry_type'], array_merge($options['entry_options'], ['required' => true]));
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_required('entry_type')->set_defaults(['entry_options' => [], 'placeholder' => 'sylius.form.zone_member.select', 'data_class' => $this->data_class]);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_zone_member';
    }
}