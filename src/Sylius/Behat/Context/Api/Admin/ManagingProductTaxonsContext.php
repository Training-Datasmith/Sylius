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

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Taxon_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Taxons_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Iri_Converter_Interface $iri_converter, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('/^I am browsing the (\d+)(?:st|nd|rd|th) page of products from ("([^"]+)" taxon)$/')]
    #[When('/^I go to the (\d+)(?:st|nd|rd|th) page of products from ("([^"]+)" taxon)$/')]
    public function i_am_browsing_the_page_of_products_from_taxon(int $page, Taxon_Interface $taxon): void
    {
        $this->i_am_browsing_products_from_taxon($taxon);
        $this->client->add_filter('page', $page);
        $this->client->filter();
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('/^I am browsing products from ("([^"]+)" taxon)$/')]
    public function i_am_browsing_products_from_taxon(Taxon_Interface $taxon): void
    {
        $this->client->index(Resources::PRODUCT_TAXONS);
        $this->client->add_filter('taxon.code', $taxon->get_code());
        $this->client->add_filter('itemsPerPage', 10);
        $this->client->filter();
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('I filter them by :product product')]
    public function i_filter_them_by_product(Product_Interface $product): void
    {
        $this->client->add_filter('product.code', $product->get_code());
        $this->client->filter();
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('I set the position of :product to :position')]
    public function i_set_the_position_of_product_to(Product_Interface $product, int|string $position): void
    {
        $this->client->build_update_request(Resources::PRODUCT_TAXONS, (string) $product->get_product_taxons()->current()->get_id());
        $this->client->update_request_data(['position' => is_numeric($position) ? (int) $position : $position]);
    }
    #[When('I (try to) add :taxon taxon to the :product product')]
    public function i_add_taxon_to_the_product(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $this->client->build_create_request(Resources::PRODUCT_TAXONS);
        $this->client->add_request_data('taxon', $this->iri_converter->get_iri_from_resource($taxon));
        $this->client->add_request_data('product', $this->iri_converter->get_iri_from_resource($product));
        $this->client->create();
    }
    #[When('I try to assign an empty taxon to the :product product')]
    public function i_try_to_assign_an_empty_taxon_to_the_product(Product_Interface $product): void
    {
        $this->client->build_create_request(Resources::PRODUCT_TAXONS);
        $this->client->add_request_data('product', $this->iri_converter->get_iri_from_resource($product));
        $this->client->create();
    }
    #[When('I try to assign an empty product to the :taxon taxon')]
    public function i_try_to_assign_an_empty_product_to_the_taxon(Taxon_Interface $taxon): void
    {
        $this->client->build_create_request(Resources::PRODUCT_TAXONS);
        $this->client->add_request_data('taxon', $this->iri_converter->get_iri_from_resource($taxon));
        $this->client->create();
    }
    #[When('/^I try to assign the product taxon of (product "[^"]+") and (taxon "[^"]+") to the (product "[^"]+")$/')]
    public function i_try_to_assign_the_product_taxon_of_product_and_taxon_to_the_product(Product_Interface $product_taxon_product, Taxon_Interface $product_taxon_taxon): void
    {
        $this->i_add_taxon_to_the_product($product_taxon_product, $product_taxon_taxon);
    }
    #[When('I change that the :product product does not belong to the :taxon taxon')]
    public function i_change_that_the_product_does_not_belong_to_the_taxon(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $product_taxon = $product->get_product_taxons()->filter(fn(Product_Taxon_Interface $product_taxon): bool => $taxon === $product_taxon->get_taxon())->first();
        $this->client->delete(Resources::PRODUCT_TAXONS, (string) $product_taxon->get_id());
    }
    #[When('I sort this taxon\'s products :order by :field')]
    public function i_sort_products_by(string $order, string $field): void
    {
        $this->client->sort([$field => Managing_Products_Context::SORT_TYPES[$order]]);
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('I save my new configuration')]
    public function i_save_my_new_configuration(): void
    {
        $this->client->update();
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[Then('/^the (first|last) product on the list within this taxon should have name "([^"]+)"$/')]
    public function the_last_product_on_the_list_within_this_taxon_should_have_name(string $position, string $name): void
    {
        $product_taxons = $this->response_checker->get_collection($this->shared_storage->get('response'));
        $product_taxon = $position === 'last' ? end($product_taxons) : reset($product_taxons);
        /** @var ProductInterface $product */
        $product = $this->iri_converter->get_resource_from_iri($product_taxon['product']);
        Assert::same($product->get_translation()->get_name(), $name);
    }
    #[Then('I should be notified that specifying a :part is required')]
    public function i_should_be_notified_that_specifying_a_is_required(string $part): void
    {
        Assert::contains($this->client->get_last_response()->get_content(), sprintf('Please select a %s.', $part));
    }
    #[Then('I should be notified that product taxons cannot be duplicated')]
    public function i_should_be_notified_that_product_taxons_cannot_be_duplicated(): void
    {
        Assert::contains($this->client->get_last_response()->get_content(), 'Product taxons cannot be duplicated.');
    }
    #[Then('I should be notified that the position :position is invalid')]
    public function i_should_be_notified_that_the_position_is_invalid(): void
    {
        Assert::contains((string) $this->response_checker->get_error($this->client->get_last_response()), 'The type of the "position" attribute must be "int", "string" given.');
    }
    #[Then('I should see the :taxon taxon')]
    public function i_should_see_the_taxon(Taxon_Interface $taxon): void
    {
        Assert::true($this->is_taxon_visible($taxon), sprintf('Taxon with code %s does not exist, but it should', $taxon->get_code()));
    }
    #[Then('I should see the :product product')]
    public function i_should_see_the_product(Product_Interface $product): void
    {
        Assert::true($this->is_product_visible($product), sprintf('Product with code %s does not exist, but it should', $product->get_code()));
    }
    #[Then('I should not see the :taxon taxon')]
    public function i_should_not_see_the_taxon(Taxon_Interface $taxon): void
    {
        Assert::false($this->is_taxon_visible($taxon), sprintf('Taxon with code %s exists, but it should not', $taxon->get_code()));
    }
    #[Then('I should not see the :product product')]
    public function i_should_not_see_the_product(Product_Interface $product): void
    {
        Assert::false($this->is_product_visible($product), sprintf('Product with code %s does not exist, but it should not', $product->get_code()));
    }
    private function is_taxon_visible(Taxon_Interface $taxon): bool
    {
        return in_array($this->iri_converter->get_iri_from_resource($taxon), array_column($this->response_checker->get_collection($this->shared_storage->get('response')), 'taxon'));
    }
    private function is_product_visible(Product_Interface $product): bool
    {
        return in_array($this->iri_converter->get_iri_from_resource($product), array_column($this->response_checker->get_collection($this->shared_storage->get('response')), 'product'));
    }
}