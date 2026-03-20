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
namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Repository\Product_Association_Repository_Interface;
use Sylius\Component\Product\Model\Product_Association_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Sylius\Component\Product\Model\Product_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Associations_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Product_Association_Repository_Interface $association_repository, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('/^I (associate as "[^"]+") the (product "[^"]+") with the ("[^"]+" product)$/')]
    public function i_associate_as_type_the_product_with_the_product(Product_Association_Type_Interface $type, Product_Interface $owner, Product_Interface $product): void
    {
        $this->i_associate_as_type_the_product_with_the_products($type, $owner, [$product]);
    }
    #[When('/^I (associate as "[^"]+") the (product "[^"]+") with the (products "[^"]+" and "[^"]+")$/')]
    public function i_associate_as_type_the_product_with_the_products(Product_Association_Type_Interface $type, Product_Interface $owner, array $products): void
    {
        $associated_products_data = [];
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $associated_products_data[] = $this->iri_converter->get_iri_from_resource($product);
        }
        $this->client->build_create_request(Resources::PRODUCT_ASSOCIATIONS);
        $this->client->add_request_data('type', $this->iri_converter->get_iri_from_resource($type));
        $this->client->add_request_data('owner', $this->iri_converter->get_iri_from_resource($owner));
        $this->client->add_request_data('associatedProducts', $associated_products_data);
        $this->client->create();
        /** @var ProductAssociationInterface $association */
        $association = $this->association_repository->find_one_by(['owner' => $owner, 'type' => $type]);
        $this->shared_storage->set('association', $association);
        $this->shared_storage->set('product', $association->get_owner());
    }
    #[When('/^I add the (product "[^"]+") to (this product association)$/')]
    public function i_add_the_product_to_this_product_association(Product_Interface $product, Product_Association_Interface $association): void
    {
        $this->client->build_update_request(Resources::PRODUCT_ASSOCIATIONS, (string) $association->get_id());
        $associated_products = [$this->iri_converter->get_iri_from_resource($product)];
        foreach ($association->get_associated_products() as $associated_product) {
            $associated_products[] = $this->iri_converter->get_iri_from_resource($associated_product);
        }
        $this->client->set_request_data(['associatedProducts' => $associated_products]);
        $this->client->update();
        $this->shared_storage->set('association', $association);
    }
    #[When('/^I change (this product association)\'s product to the ("[^"]+" product)$/')]
    public function i_change_this_product_association_product_to_product(Product_Association_Interface $association, Product_Interface $product): void
    {
        $this->client->build_update_request(Resources::PRODUCT_ASSOCIATIONS, (string) $association->get_id());
        $this->client->add_request_data('associatedProducts', [$this->iri_converter->get_iri_from_resource($product)]);
        $this->client->update();
        $this->shared_storage->set('association', $association);
    }
    #[When('/^I remove the (product "[^"]+") from (this product association)$/')]
    public function i_remove_the_product_from_this_product_association(Product_Interface $product, Product_Association_Interface $association): void
    {
        $this->client->build_update_request(Resources::PRODUCT_ASSOCIATIONS, (string) $association->get_id());
        $associated_products = [];
        foreach ($association->get_associated_products() as $associated_product) {
            if ($associated_product->get_code() !== $product->get_code()) {
                $associated_products[] = $this->iri_converter->get_iri_from_resource($associated_product);
            }
        }
        $this->client->set_request_data(['associatedProducts' => $associated_products]);
        $this->client->update();
        $this->shared_storage->set('association', $association);
    }
    #[Then('/^(this product) should have an (association "[^"]+")$/')]
    public function this_product_should_have_an_association(Product_Interface $product, Product_Association_Type_Interface $type): void
    {
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        $associations = $this->response_checker->get_value($response, 'associations');
        $association_type_iri = $this->iri_converter->get_iri_from_resource_in_section($type, 'admin');
        foreach ($associations as $association_iri) {
            $response = $this->client->show_by_iri($association_iri);
            $product_association_type = $this->response_checker->get_value($response, 'type');
            if ($association_type_iri === $product_association_type) {
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('Product %s does not have an association of type %s', $product->get_code(), $type->get_name()));
    }
    #[Then('/^(this association) should only have (product "[^"]+")$/')]
    public function this_association_should_only_have_product(Product_Association_Interface $association, Product_Interface $product): void
    {
        $this->this_association_should_have_products($association, [$product]);
    }
    #[Then('/^(this association) should have (products "[^"]+" and "[^"]+")$/')]
    public function this_association_should_have_products(Product_Association_Interface $association, array $products): void
    {
        $response = $this->client->show(Resources::PRODUCT_ASSOCIATIONS, (string) $association->get_id());
        $content = $this->response_checker->get_response_content($response);
        $associated_products = $content['associatedProducts'];
        Assert::count($associated_products, count($products));
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            Assert::in_array($this->iri_converter->get_iri_from_resource_in_section($product, 'admin'), $associated_products);
        }
    }
}