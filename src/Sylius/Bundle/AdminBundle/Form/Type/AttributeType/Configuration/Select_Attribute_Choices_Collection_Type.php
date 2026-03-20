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

use Sylius\Bundle\Attribute_Bundle\Form\Type\Attribute_Type\Configuration\Select_Attribute_Choices_Collection_Type as BaseSelectAttributeChoicesCollectionType;
use Symfony\UX\Live_Component\Form\Type\Live_Collection_Type;
class Select_Attribute_Choices_Collection_Type extends Base_Select_Attribute_Choices_Collection_Type
{
    public function get_parent(): string
    {
        return Live_Collection_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_select_attribute_choices_collection';
    }
}