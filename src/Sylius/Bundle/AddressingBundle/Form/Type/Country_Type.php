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

use Sylius\Bundle\Resource_Bundle\Form\Event_Subscriber\Add_Code_Form_Subscriber;
use Sylius\Bundle\Resource_Bundle\Form\Type\Abstract_Resource_Type;
use Symfony\Component\Form\Extension\Core\Type\Collection_Type;
use Symfony\Component\Form\Form_Builder_Interface;
final class Country_Type extends Abstract_Resource_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_event_subscriber(new Add_Code_Form_Subscriber(\Symfony\Component\Form\Extension\Core\Type\Country_Type::class))->add('provinces', Collection_Type::class, ['entry_type' => Province_Type::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'button_add_label' => 'sylius.form.country.add_province']);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_country';
    }
}