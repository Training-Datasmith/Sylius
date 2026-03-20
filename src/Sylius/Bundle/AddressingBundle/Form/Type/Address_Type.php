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
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Address_Type extends Abstract_Resource_Type
{
    /**
     * @param string[] $validationGroups
     */
    public function __construct(string $data_class, array $validation_groups, private readonly Event_Subscriber_Interface $build_address_form_subscriber)
    {
        parent::__construct($data_class, $validation_groups);
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('firstName', Text_Type::class, ['label' => 'sylius.form.address.first_name'])->add('lastName', Text_Type::class, ['label' => 'sylius.form.address.last_name'])->add('phoneNumber', Text_Type::class, ['required' => false, 'label' => 'sylius.form.address.phone_number'])->add('company', Text_Type::class, ['required' => false, 'label' => 'sylius.form.address.company'])->add('countryCode', Country_Code_Choice_Type::class, ['label' => 'sylius.form.address.country', 'enabled' => true])->add('street', Text_Type::class, ['label' => 'sylius.form.address.street'])->add('city', Text_Type::class, ['label' => 'sylius.form.address.city'])->add('postcode', Text_Type::class, ['label' => 'sylius.form.address.postcode']);
        if ($options['add_build_address_form_subscriber']) {
            $builder->add_event_subscriber($this->build_address_form_subscriber);
        }
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        parent::configure_options($resolver);
        $resolver->set_defaults(['validation_groups' => function (Options $options) {
            if ($options['shippable']) {
                return array_merge($this->validation_groups, ['shippable']);
            }
            return $this->validation_groups;
        }, 'shippable' => false, 'add_build_address_form_subscriber' => true])->set_allowed_types('shippable', 'bool');
    }
    public function get_block_prefix(): string
    {
        return 'sylius_address';
    }
}