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

use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Reversed_Transformer;
final class Country_Code_Choice_Type extends Abstract_Type
{
    /** @param RepositoryInterface<CountryInterface> $countryRepository */
    public function __construct(private readonly Repository_Interface $country_repository)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_model_transformer(new Reversed_Transformer(new Resource_To_Identifier_Transformer($this->country_repository, 'code')));
    }
    public function get_parent(): string
    {
        return Country_Choice_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_country_code_choice';
    }
}