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
use Sylius\Bundle\Core_Bundle\Form\Type\Promotion\Rule\Total_Of_Items_From_Taxon_Configuration_Type;
use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Taxonomy\Repository\Taxon_Repository_Interface;
use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Reversed_Transformer;
final class Total_Of_Items_From_Taxon_Configuration_Type_Extension extends Abstract_Type_Extension
{
    /** @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository */
    public function __construct(private readonly Taxon_Repository_Interface $taxon_repository)
    {
    }
    /** @param array<string, mixed> $options */
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('taxon', Taxon_Autocomplete_Type::class, ['label' => 'sylius.form.promotion_rule.total_of_items_from_taxon.taxon'])->get('taxon')->add_model_transformer(new Reversed_Transformer(new Resource_To_Identifier_Transformer($this->taxon_repository, 'code')));
    }
    /** @return iterable<class-string> */
    public static function get_extended_types(): iterable
    {
        return [Total_Of_Items_From_Taxon_Configuration_Type::class];
    }
}