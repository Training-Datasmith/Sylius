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
namespace Sylius\Bundle\Admin_Bundle\Form\Type\Catalog_Promotion_Scope;

use Sylius\Bundle\Admin_Bundle\Form\Type\Product_Variant_Autocomplete_Type;
use Sylius\Bundle\Core_Bundle\Form\Type\Catalog_Promotion_Scope\For_Variants_Scope_Configuration_Type as BaseForVariantsScopeConfigurationType;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Data_Transformer_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
final class For_Variants_Scope_Configuration_Type extends Abstract_Type
{
    /** @param DataTransformerInterface<ProductVariantInterface, string|null> $productVariantsToCodesTransformer */
    public function __construct(private readonly Data_Transformer_Interface $product_variants_to_codes_transformer)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('variants', Product_Variant_Autocomplete_Type::class, ['label' => 'sylius.ui.variants', 'multiple' => true, 'required' => false])->get('variants')->add_model_transformer($this->product_variants_to_codes_transformer);
    }
    public function get_parent(): string
    {
        return Base_For_Variants_Scope_Configuration_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_catalog_promotion_scope_variant_configuration';
    }
}