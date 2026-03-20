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

use Sylius\Bundle\Promotion_Bundle\Form\Type\Catalog_Promotion_Scope_Type as BaseCatalogPromotionScopeType;
use Sylius\Component\Promotion\Model\Catalog_Promotion_Scope_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Hidden_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
final class Catalog_Promotion_Scope_Type extends Abstract_Type
{
    /** @var array<string, string> */
    private array $scope_configuration_types;
    /**
     * @param iterable<string, object> $scopeConfigurationTypes
     */
    public function __construct(iterable $scope_configuration_types)
    {
        foreach ($scope_configuration_types as $type => $form_type) {
            $this->scope_configuration_types[$type] = $form_type::class;
        }
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('type', Hidden_Type::class);
        $builder->add_event_listener(Form_Events::PRE_SET_DATA, function (Form_Event $event): void {
            $this->add_scope_to_form($event);
        })->add_event_listener(Form_Events::PRE_SUBMIT, function (Form_Event $event): void {
            $this->add_scope_to_form($event);
        });
    }
    public function get_parent(): string
    {
        return Base_Catalog_Promotion_Scope_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_catalog_promotion_scope';
    }
    private function add_scope_to_form(Form_Event $event): void
    {
        $data = $event->get_data();
        if ($data === null) {
            return;
        }
        $data_type = $data instanceof Catalog_Promotion_Scope_Interface ? $data->get_type() : $data['type'];
        $scope_configuration_type = $this->scope_configuration_types[$data_type];
        $form = $event->get_form();
        $form->add('configuration', $scope_configuration_type);
    }
}