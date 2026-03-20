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

use Sylius\Bundle\Addressing_Bundle\Form\Type\Country_Type as BaseCountryType;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Type;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Checkbox_Type;
use Symfony\Component\Form\Extension\Core\Type\Country_Type as SymfonyCountryType;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfony\Component\Intl\Countries;
use Symfony\UX\Live_Component\Form\Type\Live_Collection_Type;
final class Country_Type extends Abstract_Type
{
    /** @param RepositoryInterface<CountryInterface> $countryRepository */
    public function __construct(private readonly Repository_Interface $country_repository)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_event_listener(Form_Events::PRE_SET_DATA, function (Form_Event $event): void {
            $options = ['label' => 'sylius.form.country.name', 'choice_loader' => null];
            $country = $event->get_data();
            if ($country instanceof Country_Interface && null !== $country->get_code()) {
                $options['disabled'] = true;
                $options['choices'] = [$this->get_country_name($country->get_code()) => $country->get_code()];
            } else {
                $options['choices'] = array_flip($this->get_available_countries());
            }
            $form = $event->get_form();
            $form->add('code', Symfony_Country_Type::class, $options);
        });
        $builder->add('provinces', Live_Collection_Type::class, ['entry_type' => Province_Type::class, 'label' => 'sylius.form.country.provinces', 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'button_add_options' => ['label' => 'sylius.form.country.add_province']])->add('enabled', Checkbox_Type::class, ['label' => 'sylius.form.country.enabled']);
    }
    public function get_parent(): string
    {
        return Base_Country_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_country';
    }
    private function get_country_name(string $code): string
    {
        return Countries::get_name($code);
    }
    /** @return string[] */
    private function get_available_countries(): array
    {
        $available_countries = Countries::get_names();
        /** @var CountryInterface[] $definedCountries */
        $defined_countries = $this->country_repository->find_all();
        foreach ($defined_countries as $country) {
            unset($available_countries[$country->get_code()]);
        }
        return $available_countries;
    }
}