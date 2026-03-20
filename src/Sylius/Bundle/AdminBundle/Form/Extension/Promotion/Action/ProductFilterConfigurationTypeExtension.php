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
namespace Sylius\Bundle\Admin_Bundle\Form\Extension\Promotion\Action;

use Sylius\Bundle\Admin_Bundle\Form\Type\Product_Autocomplete_Type;
use Sylius\Bundle\Core_Bundle\Form\Type\Promotion\Filter\Product_Filter_Configuration_Type;
use Sylius\Component\Core\Model\Product_Interface;
use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Data_Transformer_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
final class Product_Filter_Configuration_Type_Extension extends Abstract_Type_Extension
{
    /** @param DataTransformerInterface<ProductInterface, string|null> $productsToCodesTransformer */
    public function __construct(private readonly Data_Transformer_Interface $products_to_codes_transformer)
    {
    }
    /** @param array<string, mixed> $options */
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('products', Product_Autocomplete_Type::class, ['label' => 'sylius.form.promotion_filter.products', 'multiple' => true])->get('products')->add_model_transformer($this->products_to_codes_transformer);
    }
    /** @return iterable<class-string> */
    public static function get_extended_types(): iterable
    {
        return [Product_Filter_Configuration_Type::class];
    }
}