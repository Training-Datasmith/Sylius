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
namespace Sylius\Bundle\Admin_Bundle\Form\Extension\Promotion\Rule;

use Sylius\Bundle\Admin_Bundle\Form\Type\Taxon_Autocomplete_Type;
use Sylius\Bundle\Core_Bundle\Form\Type\Promotion\Rule\Has_Taxon_Configuration_Type;
use Sylius\Component\Core\Model\Taxon_Interface;
use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Data_Transformer_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
final class Has_Taxon_Configuration_Type_Extension extends Abstract_Type_Extension
{
    /** @param DataTransformerInterface<TaxonInterface, string|null> $taxonsToCodesTransformer */
    public function __construct(private readonly Data_Transformer_Interface $taxons_to_codes_transformer)
    {
    }
    /** @param array<string, mixed> $options */
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('taxons', Taxon_Autocomplete_Type::class, ['label' => 'sylius.form.promotion_rule.has_taxon.taxons', 'multiple' => true])->get('taxons')->add_model_transformer($this->taxons_to_codes_transformer);
    }
    /** @return iterable<class-string> */
    public static function get_extended_types(): iterable
    {
        return [Has_Taxon_Configuration_Type::class];
    }
}