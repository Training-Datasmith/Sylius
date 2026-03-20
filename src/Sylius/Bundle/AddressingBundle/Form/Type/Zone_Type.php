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

use Sylius\Bundle\Addressing_Bundle\Form\Event_Listener\Build_Zone_Form_Subscriber;
use Sylius\Bundle\Resource_Bundle\Form\Event_Subscriber\Add_Code_Form_Subscriber;
use Sylius\Bundle\Resource_Bundle\Form\Type\Abstract_Resource_Type;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Symfony\Component\Form\Extension\Core\Type\Choice_Type;
use Symfony\Component\Form\Extension\Core\Type\Collection_Type;
use Symfony\Component\Form\Extension\Core\Type\Integer_Type;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Zone_Type extends Abstract_Resource_Type
{
    /**
     * @param string[] $validationGroups
     * @param string[] $scopeChoices
     */
    public function __construct(string $data_class, array $validation_groups, private readonly array $scope_choices = [])
    {
        parent::__construct($data_class, $validation_groups);
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_event_subscriber(new Add_Code_Form_Subscriber())->add('name', Text_Type::class, ['label' => 'sylius.form.zone.name'])->add('type', Zone_Type_Choice_Type::class, ['disabled' => true])->add('priority', Integer_Type::class, ['label' => 'sylius.form.zone.priority', 'required' => true]);
        if (!empty($this->scope_choices)) {
            $builder->add('scope', Choice_Type::class, ['choices' => array_flip($this->scope_choices), 'label' => 'sylius.form.zone.scope', 'placeholder' => null, 'required' => false]);
        }
        $builder->add_event_listener(Form_Events::PRE_SET_DATA, function (Form_Event $event): void {
            /** @var ZoneInterface $zone */
            $zone = $event->get_data();
            $entry_options = ['entry_type' => $this->get_zone_member_entry_type($zone->get_type()), 'entry_options' => $this->get_zone_member_entry_options($zone->get_type())];
            if ($zone->get_type() === Zone_Interface::TYPE_ZONE) {
                $entry_options['entry_options']['choice_filter'] = static fn(?Zone_Interface $sub_zone): bool => $sub_zone !== null && $zone->get_id() !== $sub_zone->get_id();
            }
            $event->get_form()->add('members', Collection_Type::class, ['entry_type' => Zone_Member_Type::class, 'entry_options' => $entry_options, 'button_add_label' => 'sylius.form.zone.add_member', 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'delete_empty' => true]);
        });
        if ($options['add_build_zone_form_subscriber']) {
            $builder->add_event_subscriber(new Build_Zone_Form_Subscriber());
        }
    }
    public function get_block_prefix(): string
    {
        return 'sylius_zone';
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        parent::configure_options($resolver);
        $resolver->set_defaults(['add_build_zone_form_subscriber' => true]);
    }
    private function get_zone_member_entry_type(string $zone_member_type): string
    {
        $zone_member_entry_types = [Zone_Interface::TYPE_COUNTRY => Country_Code_Choice_Type::class, Zone_Interface::TYPE_PROVINCE => Province_Code_Choice_Type::class, Zone_Interface::TYPE_ZONE => Zone_Code_Choice_Type::class];
        return $zone_member_entry_types[$zone_member_type];
    }
    /** @return array<string, string|bool|array<string, string>> */
    private function get_zone_member_entry_options(string $zone_member_type): array
    {
        $zone_member_entry_options = [Zone_Interface::TYPE_COUNTRY => ['label' => 'sylius.form.zone.types.country', 'enabled' => false, 'attr' => ['class' => 'country_search_dropdown ui fluid search selection dropdown']], Zone_Interface::TYPE_PROVINCE => ['label' => 'sylius.form.zone.types.province'], Zone_Interface::TYPE_ZONE => ['label' => 'sylius.form.zone.types.zone']];
        return $zone_member_entry_options[$zone_member_type];
    }
}