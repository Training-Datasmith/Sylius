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

use Sylius\Bundle\Admin_Bundle\Form\Type\Product_Autocomplete_Type;
use Sylius\Bundle\Core_Bundle\Form\Type\Promotion\Rule\Contains_Product_Configuration_Type;
use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Repository\Product_Repository_Interface;
use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Reversed_Transformer;
final class Contains_Product_Configuration_Type_Extension extends Abstract_Type_Extension
{
    /** @param ProductRepositoryInterface<ProductInterface> $productRepository */
    public function __construct(private readonly Product_Repository_Interface $product_repository)
    {
    }
    /** @param array<string, mixed> $options */
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('product_code', Product_Autocomplete_Type::class, ['label' => 'sylius.form.promotion_action.add_product_configuration.product'])->get('product_code')->add_model_transformer(new Reversed_Transformer(new Resource_To_Identifier_Transformer($this->product_repository, 'code')));
    }
    /** @return iterable<class-string> */
    public static function get_extended_types(): iterable
    {
        return [Contains_Product_Configuration_Type::class];
    }
}