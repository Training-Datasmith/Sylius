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

use Sylius\Component\Addressing\Model\Zone_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Choice_Type;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Zone_Type_Choice_Type extends Abstract_Type
{
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['choices' => ['sylius.form.zone.types.country' => Zone_Interface::TYPE_COUNTRY, 'sylius.form.zone.types.province' => Zone_Interface::TYPE_PROVINCE, 'sylius.form.zone.types.zone' => Zone_Interface::TYPE_ZONE], 'label' => 'sylius.form.zone.type']);
    }
    public function get_parent(): string
    {
        return Choice_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_zone_type_choice';
    }
}