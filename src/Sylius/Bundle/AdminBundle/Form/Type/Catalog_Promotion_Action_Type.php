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

use Sylius\Bundle\Promotion_Bundle\Form\Type\Catalog_Promotion_Action_Type as BaseCatalogPromotionActionType;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Hidden_Type;
use Symfony\Component\Form\Form_Builder_Interface;
final class Catalog_Promotion_Action_Type extends Abstract_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('type', Hidden_Type::class);
    }
    public function get_parent(): string
    {
        return Base_Catalog_Promotion_Action_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_catalog_promotion_action';
    }
}