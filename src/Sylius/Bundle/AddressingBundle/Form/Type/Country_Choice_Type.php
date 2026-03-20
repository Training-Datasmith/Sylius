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

use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Bridge\Doctrine\Form\Data_Transformer\Collection_To_Array_Transformer;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Choice_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Country_Choice_Type extends Abstract_Type
{
    /** @param RepositoryInterface<CountryInterface> $countryRepository */
    public function __construct(private readonly Repository_Interface $country_repository)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        if ($options['multiple']) {
            $builder->add_model_transformer(new Collection_To_Array_Transformer());
        }
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['choice_filter' => null, 'choices' => function (Options $options): iterable {
            if ($options['enabled'] === true) {
                return $this->country_repository->find_by(['enabled' => $options['enabled']]);
            }
            return $this->country_repository->find_all();
        }, 'choice_value' => 'code', 'choice_label' => 'name', 'choice_translation_domain' => false, 'enabled' => true, 'label' => 'sylius.form.address.country', 'placeholder' => 'sylius.form.country.select'])->set_allowed_types('choice_filter', ['null', 'callable'])->set_normalizer('choices', static function (Options $options, array $countries): array {
            if ($options['choice_filter']) {
                $countries = array_filter($countries, $options['choice_filter']);
            }
            usort($countries, static fn(Country_Interface $first_country, Country_Interface $second_country): int => $first_country->get_name() <=> $second_country->get_name());
            return $countries;
        });
    }
    public function get_parent(): string
    {
        return Choice_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_country_choice';
    }
}