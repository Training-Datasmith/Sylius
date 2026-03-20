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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Taxon_Image_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
use Webmozart\Assert\Assert;
final readonly class Managing_Taxon_Images_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private \ArrayAccess $mink_parameters)
    {
    }
    #[When('/^I attach the "([^"]+)" image with "([^"]+)" type to (this taxon)$/')]
    public function i_attach_the_image_with_type_to_this_taxon(string $path, ?string $type, Taxon_Interface $taxon): void
    {
        $builder = Request_Builder::create_post(sprintf('/api/v2/admin/taxons/%s/images', $taxon->get_code()));
        $builder->with_header('CONTENT_TYPE', 'multipart/form-data');
        $builder->with_header('HTTP_ACCEPT', 'application/ld+json');
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_file('file', new Uploaded_File($this->mink_parameters['files_path'] . $path, basename($path)));
        if (null !== $type) {
            $builder->with_parameter('type', $type);
        }
        $this->client->request($builder->build());
    }
    #[When('/^I attach the "([^"]+)" image to (this taxon)$/')]
    public function i_attach_the_image_to_this_taxon(string $path, Taxon_Interface $taxon): void
    {
        $this->i_attach_the_image_with_type_to_this_taxon($path, null, $taxon);
    }
    #[When('I( also) remove an image with :type type')]
    public function i_remove_an_image_with_type(string $type): void
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->shared_storage->get('taxon');
        /** @var TaxonImageInterface $taxonImage */
        $taxon_image = $taxon->get_images_by_type($type)->first();
        Assert::not_false($taxon_image);
        $this->remove_taxon_image($taxon, $taxon_image);
    }
    #[When('I remove the first image')]
    public function i_remove_the_first_image(): void
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->shared_storage->get('taxon');
        /** @var TaxonImageInterface $taxonImage */
        $taxon_image = $taxon->get_images()->first();
        Assert::not_false($taxon_image);
        $this->remove_taxon_image($taxon, $taxon_image);
    }
    #[When('I change the first image type to :type')]
    public function i_change_the_first_image_type_to(string $type): void
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->shared_storage->get('taxon');
        $taxon_image = $taxon->get_images()->first();
        Assert::not_false($taxon_image);
        $builder = Request_Builder::create_put(sprintf('/api/v2/admin/taxons/%s/images/%s', $taxon->get_code(), $taxon_image->get_id()));
        $builder->with_content(['type' => $type]);
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_header('CONTENT_TYPE', 'application/ld+json');
        $this->client->request($builder->build());
    }
    #[Then('I should be notified that the changes have been successfully applied')]
    public function i_should_be_notified_that_the_changes_have_been_successfully_applied(): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->is_deletion_successful($response) || $this->response_checker->is_update_successful($response));
    }
    #[Then('/^(this taxon) should(?:| also) have an image with "([^"]*)" type$/')]
    #[Then('/^(it) should(?:| also) have an image with "([^"]*)" type$/')]
    public function this_taxon_should_have_an_image_with_type(Taxon_Interface $taxon, string $type): void
    {
        Assert::true($this->response_checker->has_values_in_any_subresource_object_collection($this->client->show(Resources::TAXONS, $taxon->get_code()), 'images', ['type' => $type]));
    }
    #[Then('/^(this taxon) should not have(?:| also) any images with "([^"]*)" type$/')]
    #[Then('/^(it) should not have(?:| also) any images with "([^"]*)" type$/')]
    public function this_taxon_should_not_have_any_images_with_type(Taxon_Interface $taxon, string $type): void
    {
        Assert::false($this->response_checker->has_values_in_any_subresource_object_collection($this->client->show(Resources::TAXONS, $taxon->get_code()), 'images', ['type' => $type]));
    }
    #[Then('/^(this taxon) should have only one image$/')]
    #[Then('/^(this taxon) should(?:| still) have (\d+) images?$/')]
    public function this_taxon_should_have_images(Taxon_Interface $taxon, int $count = 1): void
    {
        Assert::count($this->response_checker->get_value($this->client->show(Resources::TAXONS, $taxon->get_code()), 'images'), $count);
    }
    #[Then('/^(this taxon) should not have any images$/')]
    public function this_taxon_should_not_have_any_images(Taxon_Interface $taxon): void
    {
        $this->this_taxon_should_have_images($taxon, 0);
    }
    private function remove_taxon_image(Taxon_Interface $taxon, Taxon_Image_Interface $taxon_image): void
    {
        $builder = Request_Builder::create_delete(sprintf('/api/v2/admin/taxons/%s/images/%s', $taxon->get_code(), $taxon_image->get_id()));
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_header('CONTENT_TYPE', 'application/ld+json');
        $this->client->request($builder->build());
    }
}