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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Product\Model\Product_Attribute_Interface;
use Sylius\Component\Product\Model\Product_Attribute_Translation_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Attribute_Context implements Context
{
    public function __construct(private Repository_Interface $product_attribute_translation_repository)
    {
    }
    #[Transform(':attribute')]
    #[Transform(':productAttribute')]
    #[Transform('/^"([^"]+)" product attribute$/')]
    public function get_product_attribute_by_name(string $name): Product_Attribute_Interface
    {
        /** @var ProductAttributeTranslationInterface[] $productAttributeTranslations */
        $product_attribute_translations = $this->product_attribute_translation_repository->find_by(['name' => $name]);
        Assert::not_empty($product_attribute_translations, sprintf('Product attribute with with name "%s" does not exist', $name));
        /** @var ProductAttributeInterface $productAttribute */
        $product_attribute = $product_attribute_translations[0]->get_translatable();
        return $product_attribute;
    }
}