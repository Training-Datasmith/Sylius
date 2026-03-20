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
namespace Sylius\Bundle\Admin_Bundle\Form\Type\Attribute_Type\Configuration;

use Sylius\Bundle\Attribute_Bundle\Form\Type\Attribute_Type\Configuration\Select_Attribute_Configuration_Type as BaseSelectAttributeConfigurationType;
use Sylius\Bundle\Attribute_Bundle\Form\Type\Attribute_Type\Configuration\Select_Attribute_Value_Translations_Type;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Builder_Interface;
class Select_Attribute_Configuration_Type extends Base_Select_Attribute_Configuration_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        parent::build_form($builder, $options);
        $builder->add('choices', Select_Attribute_Choices_Collection_Type::class, ['entry_type' => Select_Attribute_Value_Translations_Type::class, 'label' => 'sylius.form.attribute_type_configuration.select.values', 'allow_add' => true, 'allow_delete' => true, 'required' => false, 'entry_options' => ['entry_type' => Text_Type::class]]);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_attribute_type_configuration_select';
    }
}