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

use Doctrine\Common\Collections\Array_Collection;
use Sylius\Bundle\Admin_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Bundle\Core_Bundle\Form\Type\Channel_Price_History_Config_Type as BaseChannelPriceHistoryConfigType;
use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Recursive_Transformer;
use Sylius\Component\Core\Model\Channel_Price_History_Config_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Taxonomy\Repository\Taxon_Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Data_Mapper_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Form\Reversed_Transformer;
use Webmozart\Assert\Assert;
final class Channel_Price_History_Config_Type extends Abstract_Type implements Data_Mapper_Interface
{
    /** @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository */
    public function __construct(private readonly Taxon_Repository_Interface $taxon_repository, private readonly Data_Mapper_Interface $data_mapper)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('taxonsExcludedFromShowingLowestPrice', Taxon_Autocomplete_Type::class, ['label' => 'sylius.ui.taxons_for_which_the_lowest_price_is_not_displayed', 'required' => false, 'multiple' => true, 'expanded' => false]);
        $builder->get('taxonsExcludedFromShowingLowestPrice')->add_model_transformer(new Recursive_Transformer(new Reversed_Transformer(new Resource_To_Identifier_Transformer($this->taxon_repository, 'code'))));
        $builder->set_data_mapper($this);
    }
    public function get_parent(): string
    {
        return Base_Channel_Price_History_Config_Type::class;
    }
    public function map_data_to_forms(mixed $view_data, \Traversable $forms): void
    {
        $this->data_mapper->map_data_to_forms($view_data, $forms);
    }
    public function map_forms_to_data(\Traversable $forms, mixed &$view_data): void
    {
        Assert::is_instance_of($channel_price_history_config = $view_data, Channel_Price_History_Config_Interface::class);
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $channel_price_history_config->clear_taxons_excluded_from_showing_lowest_price();
        $excluded_taxons_form = $forms['taxonsExcludedFromShowingLowestPrice'];
        /** @var iterable<TaxonInterface> $excludedTaxons */
        $excluded_taxons = $excluded_taxons_form->get_norm_data() ?? [];
        foreach ($excluded_taxons as $taxon) {
            $channel_price_history_config->add_taxon_excluded_from_showing_lowest_price($taxon);
        }
        unset($forms['taxonsExcludedFromShowingLowestPrice']);
        $this->data_mapper->map_forms_to_data(new Array_Collection($forms), $view_data);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_channel_price_history_config';
    }
}