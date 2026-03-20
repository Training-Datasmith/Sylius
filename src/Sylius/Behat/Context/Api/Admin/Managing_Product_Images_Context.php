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
use Sylius\Behat\Client\Request_Builder;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Images_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private \ArrayAccess $mink_parameters, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('/^I attach the "([^"]+)" image with "([^"]+)" type to (this product)$/')]
    public function i_attach_the_image_with_type_to_this_product(string $path, string $type, Product_Interface $product): void
    {
        $this->create_product_image($path, $product, $type);
    }
    #[When('/^I attach the "([^"]+)" image to (this product)$/')]
    public function i_attach_the_image_to_this_product(string $path, Product_Interface $product): void
    {
        $this->create_product_image($path, $product);
    }
    #[When('/^I attach the "([^"]+)" image with selected ("[^"]+" variant) to (this product)$/')]
    public function i_attach_image_with_selected_variant_to_this_product(string $path, Product_Variant_Interface $product_variant, Product_Interface $product): void
    {
        $this->create_product_image($path, $product, null, [$product_variant]);
    }
    #[When('I( also) remove an image with :type type')]
    public function i_remove_an_image_with_type(string $type): void
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $product_image = $product->get_images_by_type($type)->first();
        Assert::not_false($product_image);
        $this->remove_product_image($product->get_code(), (string) $product_image->get_id());
    }
    #[When('I remove the first image')]
    public function i_remove_the_first_image(): void
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $product_image = $product->get_images()->first();
        Assert::not_false($product_image);
        $this->remove_product_image($product->get_code(), (string) $product_image->get_id());
    }
    #[When('I change the first image type to :type')]
    public function i_change_the_first_image_type_to(string $type): void
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $product_image = $product->get_images()->first();
        Assert::not_false($product_image);
        $builder = Request_Builder::create_put(sprintf('/api/v2/admin/products/%s/images/%s', $product->get_code(), $product_image->get_id()));
        $builder->with_content(['type' => $type]);
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_header('CONTENT_TYPE', 'application/ld+json');
        $this->client->request($builder->build());
    }
    #[When('I select :productVariant variant for the first image')]
    public function i_select_variant_for_the_first_image(Product_Variant_Interface $product_variant): void
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $product_image = $product->get_images()->first();
        Assert::not_false($product_image);
        $builder = Request_Builder::create_put(sprintf('/api/v2/admin/products/%s/images/%s', $product->get_code(), $product_image->get_id()));
        $builder->with_content(['productVariants' => [$this->iri_converter->get_iri_from_resource_in_section($product_variant, 'admin')]]);
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_header('CONTENT_TYPE', 'application/ld+json');
        $this->client->request($builder->build());
    }
    #[Then('the product :product should have an image with :type type')]
    #[Then('/^(this product) should(?:| also) have an image with "([^"]*)" type$/')]
    #[Then('/^(it) should(?:| also) have an image with "([^"]*)" type$/')]
    public function the_product_should_have_an_image_with_type(Product_Interface $product, string $type): void
    {
        Assert::true($this->response_checker->has_values_in_any_subresource_object_collection($this->client->show(Resources::PRODUCTS, $product->get_code()), 'images', ['type' => $type]), sprintf('Product %s does not have an image with %s type', $product->get_name(), $type));
    }
    #[Then('its image should have :productVariant variant selected')]
    public function its_image_should_have_variant_selected(Product_Variant_Interface $product_variant): void
    {
        $images = $this->response_checker->get_value($this->client->get_last_response(), 'images');
        Assert::not_empty($images);
        Assert::in_array($this->iri_converter->get_iri_from_resource_in_section($product_variant, 'admin'), $images[0]['productVariants']);
    }
    #[Then('/^(this product) should not have(?:| also) any images with "([^"]*)" type$/')]
    #[Then('/^(it) should not have(?:| also) any images with "([^"]*)" type$/')]
    public function this_product_should_not_have_any_images_with_type(Product_Interface $product, string $type): void
    {
        Assert::false($this->response_checker->has_values_in_any_subresource_object_collection($this->client->show(Resources::PRODUCTS, $product->get_code()), 'images', ['type' => $type]), sprintf('Product %s does not have an image with %s type', $product->get_name(), $type));
    }
    #[Then('/^(this product) should(?:| still) have only one image$/')]
    #[Then('/^(this product) should(?:| still) have (\d+) images?$/')]
    public function this_product_should_have_images(Product_Interface $product, int $count = 1): void
    {
        Assert::count($this->response_checker->get_value($this->client->show(Resources::PRODUCTS, $product->get_code()), 'images'), $count);
    }
    #[Then('/^(this product) should not have any images$/')]
    public function this_product_should_not_have_any_images(Product_Interface $product): void
    {
        $this->this_product_should_have_images($product, 0);
    }
    #[Then('I should be notified that the changes have been successfully applied')]
    public function i_should_be_notified_that_the_changes_have_been_successfully_applied(): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->is_deletion_successful($response) || $this->response_checker->is_update_successful($response));
    }
    #[Then('/^I should be notified that the ("[^"]+" variant) does not belong to (this product)$/')]
    public function i_should_be_notified_that_the_product_variant_does_not_belong_to_the_owner(Product_Variant_Interface $product_variant, Product_Interface $product): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('The product variant with code "%s" does not belong to the product with code "%s", which is the owner of the image.', $product_variant->get_code(), $product->get_code()));
    }
    #[Then('I should be notified that svg file is not allowed')]
    public function i_should_be_notified_that_svg_file_is_not_allowed(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The file is not a valid image.');
    }
    private function create_product_image(string $path, Product_Interface $product, ?string $type = null, array $variants = []): void
    {
        $builder = Request_Builder::create_post(sprintf('/api/v2/admin/products/%s/images', $product->get_code()));
        $builder->with_header('CONTENT_TYPE', 'multipart/form-data');
        $builder->with_header('HTTP_ACCEPT', 'application/ld+json');
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_file('file', new Uploaded_File($this->mink_parameters['files_path'] . $path, basename($path)));
        if (null !== $type) {
            $builder->with_parameter('type', $type);
        }
        if (0 !== count($variants)) {
            $variants_iris = [];
            foreach ($variants as $variant) {
                $variants_iris[] = $this->iri_converter->get_iri_from_resource_in_section($variant, 'admin');
            }
            $builder->with_parameter('productVariants', $variants_iris);
        }
        $this->client->request($builder->build());
    }
    private function remove_product_image(string $product_code, string $product_image_id): void
    {
        $builder = Request_Builder::create_delete(sprintf('/api/v2/admin/products/%s/images/%s', $product_code, $product_image_id));
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_header('CONTENT_TYPE', 'application/ld+json');
        $this->client->request($builder->build());
    }
}