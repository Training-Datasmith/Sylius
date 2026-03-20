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

use Sylius\Bundle\Addressing_Bundle\Form\Type\Address_Type as BaseAddressType;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Code_Choice_Type;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Repository\Country_Repository_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfonycasts\Dynamic_Forms\Dependent_Field;
use Symfonycasts\Dynamic_Forms\Dynamic_Form_Builder;
final class Address_Type extends Abstract_Type
{
    /**
     * @param CountryRepositoryInterface<CountryInterface> $countryRepository
     */
    public function __construct(private readonly Country_Repository_Interface $country_repository)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder = new Dynamic_Form_Builder($builder);
        $builder->add_dependent('provinceCode', 'countryCode', function (Dependent_Field $field, ?string $country_code = null): void {
            if (null === $country_code) {
                return;
            }
            $country = $this->country_repository->find_one_by(['code' => $country_code]);
            if ($country->has_provinces()) {
                $field->add(Province_Code_Choice_Type::class, ['country' => $country, 'placeholder' => 'sylius.form.province.select', 'label' => 'sylius.form.address.province', 'auto_initialize' => false]);
            }
        })->add_dependent('provinceName', 'countryCode', function (Dependent_Field $field, ?string $country_code = null): void {
            if (null === $country_code) {
                return;
            }
            $country = $this->country_repository->find_one_by(['code' => $country_code]);
            if (!$country->has_provinces()) {
                $field->add(Text_Type::class, ['label' => 'sylius.form.address.province', 'required' => false, 'auto_initialize' => false]);
            }
        });
        $builder->add_event_listener(Form_Events::SUBMIT, function (Form_Event $form_event): void {
            /** @var AddressInterface $data */
            $data = $form_event->get_data();
            $form = $form_event->get_form();
            $form->has('provinceCode') ?: $data->set_province_code(null);
            $form->has('provinceName') ?: $data->set_province_name(null);
        });
    }
    public function get_parent(): string
    {
        return Base_Address_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_address';
    }
}