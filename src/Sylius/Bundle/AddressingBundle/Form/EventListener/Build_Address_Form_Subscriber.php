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
namespace Sylius\Bundle\Addressing_Bundle\Form\Event_Listener;

use Doctrine\Persistence\Object_Repository;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Code_Choice_Type;
use Sylius\Component\Addressing\Model\Address_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfony\Component\Form\Form_Factory_Interface;
use Symfony\Component\Form\Form_Interface;
/**
 * @internal
 */
final readonly class Build_Address_Form_Subscriber implements Event_Subscriber_Interface
{
    public function __construct(private Object_Repository $country_repository, private Form_Factory_Interface $form_factory)
    {
    }
    public static function get_subscribed_events(): array
    {
        return [Form_Events::PRE_SET_DATA => 'preSetData', Form_Events::PRE_SUBMIT => 'preSubmit'];
    }
    public function pre_set_data(Form_Event $event): void
    {
        /** @var AddressInterface|null $address */
        $address = $event->get_data();
        if (null === $address) {
            return;
        }
        $country_code = $address->get_country_code();
        if (null === $country_code) {
            return;
        }
        /** @var CountryInterface|null $country */
        $country = $this->country_repository->find_one_by(['code' => $country_code]);
        if (null === $country) {
            return;
        }
        $form = $event->get_form();
        if ($country->has_provinces()) {
            $form->add($this->create_province_code_choice_form($country, $address->get_province_code()));
            return;
        }
        $form->add($this->create_province_name_text_form($address->get_province_name()));
    }
    public function pre_submit(Form_Event $event): void
    {
        $data = $event->get_data();
        if (!is_array($data) || !array_key_exists('countryCode', $data)) {
            return;
        }
        if ('' === $data['countryCode']) {
            return;
        }
        /** @var CountryInterface|null $country */
        $country = $this->country_repository->find_one_by(['code' => $data['countryCode']]);
        if (null === $country) {
            return;
        }
        $form = $event->get_form();
        if ($country->has_provinces()) {
            $form->add($this->create_province_code_choice_form($country));
            return;
        }
        $form->add($this->create_province_name_text_form());
    }
    private function create_province_code_choice_form(Country_Interface $country, ?string $province_code = null): Form_Interface
    {
        return $this->form_factory->create_named('provinceCode', Province_Code_Choice_Type::class, $province_code, ['country' => $country, 'auto_initialize' => false, 'label' => 'sylius.form.address.province', 'placeholder' => 'sylius.form.province.select']);
    }
    private function create_province_name_text_form(?string $province_name = null): Form_Interface
    {
        return $this->form_factory->create_named('provinceName', Text_Type::class, $province_name, ['required' => false, 'auto_initialize' => false, 'label' => 'sylius.form.address.province']);
    }
}