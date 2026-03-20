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
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Taxon_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Object_Manager $object_manager)
    {
    }
    #[When('/^I try to browse products from (taxon "([^"]+)")$/')]
    #[When('/^I check the ("[^"]+" taxon)\'s details$/')]
    public function i_try_to_browse_products_from(Taxon_Interface $taxon): void
    {
        $this->object_manager->clear();
        // avoiding doctrine cache
        $this->client->show(Resources::TAXONS, $taxon->get_code());
    }
    #[Then('I should not see :taxon in the vertical menu')]
    public function i_should_not_see_in_the_vertical_menu(Taxon_Interface $taxon): void
    {
        Assert::false($this->is_taxon_child_visible($taxon), sprintf('Taxon %s is in the vertical menu, but it should not.', $taxon->get_name()));
    }
    #[Then('I should see the taxon name :name')]
    public function i_should_see_taxon_name(string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'name', $name), sprintf('Taxon with name %s does not exist.', $name));
    }
    #[Then('/^I should see ("([^"]+)" and "([^"]+)" in the vertical menu)$/')]
    public function i_should_see_in_the_vertical_menu(iterable $taxons): void
    {
        foreach ($taxons as $taxon) {
            Assert::true($this->is_taxon_child_visible($taxon), sprintf('Taxon %s is not in the vertical menu, but it should be.', $taxon->get_name()));
        }
    }
    private function is_taxon_child_visible(Taxon_Interface $taxon): bool
    {
        $taxon_iri = $this->iri_converter->get_iri_from_resource($taxon);
        $response = $this->client->get_last_response();
        $children = $this->response_checker->get_value($response, 'children');
        return in_array($taxon_iri, $children, true);
    }
}