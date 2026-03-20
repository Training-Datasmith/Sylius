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

use Sylius\Bundle\Promotion_Bundle\Form\Type\Catalog_Promotion_Type as BaseCatalogPromotionType;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\UX\Live_Component\Form\Type\Live_Collection_Type;
final class Catalog_Promotion_Type extends Abstract_Type
{
    /**
     * @param array<string, string> $scopeTypes
     * @param array<string, string> $actionTypes
     */
    public function __construct(private readonly array $scope_types, private readonly array $action_types)
    {
    }
    /** @param array<string, mixed> $options */
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('scopes', Live_Collection_Type::class, ['entry_type' => Catalog_Promotion_Scope_Type::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'button_add_type' => Add_Button_Type::class, 'button_add_options' => ['label' => 'sylius.ui.add_scope', 'types' => $this->scope_types], 'button_delete_options' => ['label' => false]])->add('actions', Live_Collection_Type::class, ['entry_type' => Catalog_Promotion_Action_Type::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'button_add_type' => Add_Button_Type::class, 'button_add_options' => ['label' => 'sylius.ui.add_action', 'types' => $this->action_types], 'button_delete_options' => ['label' => false]]);
    }
    public function get_parent(): string
    {
        return Base_Catalog_Promotion_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_catalog_promotion';
    }
}