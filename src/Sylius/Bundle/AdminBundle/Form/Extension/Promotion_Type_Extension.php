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
namespace Sylius\Bundle\Admin_Bundle\Form\Extension;

use Sylius\Bundle\Admin_Bundle\Form\Type\Add_Button_Type;
use Sylius\Bundle\Promotion_Bundle\Form\Type\Promotion_Action_Type;
use Sylius\Bundle\Promotion_Bundle\Form\Type\Promotion_Rule_Type;
use Sylius\Bundle\Promotion_Bundle\Form\Type\Promotion_Type;
use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\UX\Live_Component\Form\Type\Live_Collection_Type;
final class Promotion_Type_Extension extends Abstract_Type_Extension
{
    /**
     * @param array<string, string> $ruleTypes
     * @param array<string, string> $actionTypes
     */
    public function __construct(private readonly array $rule_types, private readonly array $action_types)
    {
    }
    /** @param array<string, mixed> $options */
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('rules', Live_Collection_Type::class, ['entry_type' => Promotion_Rule_Type::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'button_add_type' => Add_Button_Type::class, 'button_add_options' => ['label' => 'sylius.ui.add_rule', 'types' => $this->rule_types], 'button_delete_options' => ['label' => false]])->add('actions', Live_Collection_Type::class, ['entry_type' => Promotion_Action_Type::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'button_add_type' => Add_Button_Type::class, 'button_add_options' => ['label' => 'sylius.ui.add_action', 'types' => $this->action_types], 'button_delete_options' => ['label' => false]]);
    }
    /** @return iterable<class-string> */
    public static function get_extended_types(): iterable
    {
        return [Promotion_Type::class];
    }
}