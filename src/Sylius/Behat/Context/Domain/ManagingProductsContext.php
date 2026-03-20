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
namespace Sylius\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\DBAL\Exception as DBALException;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Products_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Repository_Interface $product_repository, private Repository_Interface $product_variant_repository, private Repository_Interface $product_review_repository)
    {
    }
    #[When('/^I delete the ("[^"]+" variant of product "[^"]+")$/')]
    public function i_delete_the_variant_of_product(Product_Variant_Interface $product_variant): void
    {
        $this->product_variant_repository->remove($product_variant);
    }
    #[When('/^I try to delete the ("[^"]+" variant of product "[^"]+")$/')]
    public function i_try_to_delete_the_variant_of_product(Product_Variant_Interface $product_variant): void
    {
        try {
            $this->product_variant_repository->remove($product_variant);
        } catch (Dbal_Exception $exception) {
            $this->shared_storage->set('last_exception', $exception);
        }
    }
    #[When('/^I delete the ("[^"]+" product)$/')]
    public function i_delete_the_product(Product_Interface $product): void
    {
        $this->product_repository->remove($product);
    }
    #[When('/^I try to delete the ("[^"]+" product)$/')]
    public function i_try_to_delete_the_product(Product_Interface $product): void
    {
        try {
            $this->product_repository->remove($product);
        } catch (\Exception $exception) {
            $this->shared_storage->set('last_exception', $exception);
        }
    }
    #[Then('/^I should be notified that this (?:variant|product) is in use and cannot be deleted$/')]
    public function i_should_be_notified_that_this_product_variant_is_in_use_and_cannot_be_deleted(): void
    {
        Assert::is_instance_of($this->shared_storage->get('last_exception'), Dbal_Exception::class);
    }
    #[Then('/^(this variant) should not exist in the product catalog$/')]
    public function product_variant_should_not_exist_in_the_product_catalog(Product_Variant_Interface $product_variant): void
    {
        Assert::null($this->product_variant_repository->find_one_by(['code' => $product_variant->get_code()]));
    }
    #[Then('/^(this variant) should still exist in the product catalog$/')]
    public function product_variant_should_exist_in_the_product_catalog(Product_Variant_Interface $product_variant): void
    {
        Assert::not_null($product_variant);
    }
    #[Then('/^(this product) should still exist in the product catalog$/')]
    public function product_should_exist_in_the_product_catalog(Product_Interface $product): void
    {
        Assert::not_null($product);
    }
    #[Then('/^there should be no reviews of (this product)$/')]
    public function there_are_no_product_reviews(Product_Interface $product): void
    {
        $reviews = $this->product_review_repository->find_by(['reviewSubject' => $product]);
        Assert::same($reviews, []);
    }
    #[Then('/^there should be no variants of (this product) in the product catalog$/')]
    public function there_are_no_variants(Product_Interface $product): void
    {
        $variants = $this->product_variant_repository->find_by(['product' => $product]);
        Assert::same($variants, []);
    }
}