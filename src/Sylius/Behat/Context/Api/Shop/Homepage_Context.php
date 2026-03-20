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
namespace Sylius\Behat\Context\Api\Shop;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final readonly class Homepage_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Object_Manager $object_manager, private string $api_url_prefix)
    {
    }
    #[When('I check latest products')]
    public function i_check_latest_products(): void
    {
        $this->client->custom_action(sprintf('%s/shop/products?itemsPerPage=4&order[createdAt]=desc', $this->api_url_prefix), Http_Request::METHOD_GET);
    }
    #[Then('I should see :productName product')]
    public function i_should_see_product(string $product_name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $product_name));
    }
    #[Then('I should not see :productName product')]
    public function i_should_not_see_product(string $product_name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $product_name));
    }
    #[When('I check available taxons')]
    public function i_check_available_taxons(): void
    {
        $this->object_manager->clear();
        // avoiding doctrine cache
        $this->client->custom_action(sprintf('%s/shop/taxons', $this->api_url_prefix), Http_Request::METHOD_GET);
    }
    #[Then('I should see :count products in the list')]
    public function i_should_see_products_in_the_list(int $count): void
    {
        Assert::eq($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('I should see :firstMenuItem in the menu')]
    #[Then('I should see :firstMenuItem and :secondMenuItem in the menu')]
    public function i_should_see_and_in_the_menu(string ...$expected_menu_items): void
    {
        $menu_items = $this->get_available_taxon_menu_items_from_taxon_collection($this->client->get_last_response());
        Assert::true($this->are_all_menu_items_visible($menu_items, $expected_menu_items), sprintf('Menu items %s should be present in the menu', implode(', ', $expected_menu_items)));
    }
    #[Then('I should not see :firstMenuItem and :secondMenuItem in the menu')]
    #[Then('I should not see :firstMenuItem, :secondMenuItem and :thirdMenuItem in the menu')]
    #[Then('I should not see :firstMenuItem, :secondMenuItem, :thirdMenuItem and :fourthMenuItem in the menu')]
    public function i_should_not_see_and_in_the_menu(string ...$unexpected_menu_items): void
    {
        $menu_items = $this->get_available_taxon_menu_items_from_taxon_collection($this->client->get_last_response());
        Assert::false($this->are_all_menu_items_visible($menu_items, $unexpected_menu_items), sprintf('Menu items %s should not be present in the menu', implode(', ', $unexpected_menu_items)));
    }
    private function are_all_menu_items_visible(array $menu_items, array $expected_menu_items): bool
    {
        foreach ($expected_menu_items as $expected_menu_item) {
            if (!in_array($expected_menu_item, $menu_items)) {
                return false;
            }
        }
        return true;
    }
    private function get_available_taxon_menu_items_from_taxon_collection(Response $response): array
    {
        $taxons = $this->response_checker->get_collection($response);
        if ([] === $taxons) {
            return [];
        }
        $menu_items = array_column($taxons, 'name');
        Assert::not_empty($menu_items);
        $children = array_column($taxons, 'children');
        foreach ($children[0] as $child) {
            if (!empty($child)) {
                array_push($menu_items, $this->iri_converter->get_resource_from_iri($child)->get_name());
            }
        }
        return $menu_items;
    }
}