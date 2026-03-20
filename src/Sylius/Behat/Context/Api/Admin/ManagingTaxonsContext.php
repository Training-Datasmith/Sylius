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
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Taxons_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to see all taxons in store')]
    public function i_want_to_see_all_taxons_in_store(): void
    {
        $this->client->index(Resources::TAXONS);
    }
    #[When('I want to create a new taxon')]
    public function i_want_to_create_new_taxon(): void
    {
        $this->client->build_create_request(Resources::TAXONS);
    }
    #[When('I want to create a new taxon for :parentTaxon')]
    public function i_want_to_create_a_new_taxon_for_parent(Taxon_Interface $parent_taxon): void
    {
        $this->i_want_to_create_new_taxon();
        $this->i_set_its_parent_taxon_to($parent_taxon);
    }
    #[When('I want to modify the :taxon taxon')]
    public function i_want_to_modify_a_taxon(Taxon_Interface $taxon): void
    {
        $this->shared_storage->set('taxon', $taxon);
        $this->client->build_update_request(Resources::TAXONS, $taxon->get_code());
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        if ($code !== null) {
            $this->client->add_request_data('code', $code);
        }
    }
    #[When('I name it :name in :localeCode')]
    #[When('I rename it to :name in :localeCode')]
    #[When('I do not specify its name')]
    public function i_name_it_in(?string $name = null, string $locale_code = 'en_US'): void
    {
        $this->update_translations($locale_code, 'name', $name);
    }
    #[When('I set its slug to :slug in :localeCode')]
    public function i_set_its_slug_to(string $slug, string $locale_code): void
    {
        $this->update_translations($locale_code, 'slug', $slug);
    }
    #[When('I generate its slug in :localeCode')]
    public function i_generate_its_slug_in(string $locale_code): void
    {
        $this->update_translations($locale_code, 'slug', '');
    }
    #[When('I describe it as :description in :localeCode')]
    #[When('I change its description to :description in :localeCode')]
    public function i_describe_it_as_in(string $description, string $locale_code): void
    {
        $this->update_translations($locale_code, 'description', $description);
    }
    #[When('I set its parent taxon to :parentTaxon')]
    #[When('I change its parent taxon to :parentTaxon')]
    public function i_set_its_parent_taxon_to(Taxon_Interface $parent_taxon): void
    {
        $this->client->add_request_data('parent', $this->iri_converter->get_iri_from_resource_in_section($parent_taxon, 'admin'));
    }
    #[When('/^I (enable|disable) it$/')]
    public function i_enable_it(string $toggle_action): void
    {
        $this->client->add_request_data('enabled', $toggle_action === 'enable');
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I remove taxon named :name')]
    #[When('I delete taxon named :name')]
    #[When('I try to delete taxon named :name')]
    public function i_remove_taxon_named(string $name): void
    {
        $code = String_Inflector::name_to_lowercase_code($name);
        $this->client->delete(Resources::TAXONS, $code);
    }
    #[When('I move down :taxonName taxon')]
    public function i_move_down_taxon(string $taxon_name): void
    {
        $last_response = $this->client->get_last_response();
        $code = String_Inflector::name_to_lowercase_code($taxon_name);
        $taxon = $this->response_checker->get_collection_items_with_value($last_response, 'code', $code);
        $position = $taxon[0]['position'];
        $this->client->build_update_request(Resources::TAXONS, $code);
        $this->client->add_request_data('position', $position + 1);
        $this->client->update();
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Taxon could not be created');
    }
    #[Then('I should see the taxon named :name in the list')]
    public function i_should_see_the_taxon_named_in_the_list(string $name): void
    {
        $code = String_Inflector::name_to_lowercase_code($name);
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::TAXONS), 'code', $code));
    }
    #[Then('/^taxon named "([^"]+)" should not be added$/')]
    #[Then('the taxon named :name should no longer exist in the registry')]
    public function taxon_named_should_not_be_added(string $name): void
    {
        $code = String_Inflector::name_to_lowercase_code($name);
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::TAXONS), 'code', $code));
    }
    #[Then('/^the ("[^"]+" taxon) should appear in the registry$/')]
    public function the_taxon_should_appear_in_the_registry(Taxon_Interface $taxon): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::TAXONS), 'code', $taxon->get_code()));
        $this->shared_storage->set('taxon', $taxon);
    }
    #[Then('I should be notified that I cannot delete a menu taxon of any channel')]
    public function i_should_be_notified_that_i_cannot_delete_a_menu_taxon_of_any_channel(): void
    {
        $last_response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_deletion_successful($last_response));
    }
    #[Then('/^(it) should not belong to any other taxon$/')]
    public function it_should_not_belong_to_any_other_taxon(Taxon_Interface $taxon): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['code' => $taxon->get_code(), 'parent' => null]));
    }
    #[Then('/^(this taxon) should (belongs to "[^"]+")$/')]
    public function this_taxon_should_belongs_to(Taxon_Interface $taxon, Taxon_Interface $parent_taxon): void
    {
        $this->i_want_to_see_all_taxons_in_store();
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['code' => $taxon->get_code(), 'parent' => $this->iri_converter->get_iri_from_resource_in_section($parent_taxon, 'admin')]));
    }
    #[Then('I should see :count taxons on the list')]
    public function i_should_see_taxons_in_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('this taxon :field should be :value')]
    #[Then('this taxon should have :field :value in :localeCode')]
    public function this_taxon_field_should_be(string $field, string $value, string $locale_code = 'en_US'): void
    {
        Assert::true($this->response_checker->has_translation($this->client->get_last_response(), $locale_code, $field, $value));
    }
    #[Then('the :field of the :taxonName taxon should( still) be :value')]
    public function the_field_of_the_taxon_should_still_be(string $field, string $taxon_name, string $value): void
    {
        $this->this_taxon_field_should_be($field, $value);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The code field with value NEW_CODE exist');
    }
    #[Then('/^(it) should be (enabled|disabled)$/')]
    public function it_should_be_disabled(Taxon_Interface $taxon, string $enabled): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::TAXONS, $taxon->get_code()), 'enabled', $enabled === 'enabled'));
    }
    #[Then('I should be notified that :field is required')]
    public function i_should_be_notified_that_field_is_required(string $field): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter taxon %s.', $field));
    }
    #[Then('I should be notified that taxon slug must be unique')]
    public function i_should_be_notified_that_taxon_slug_must_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'slug: Taxon slug must be unique.');
    }
    #[Then('I should be notified that taxon with this code already exists')]
    public function i_should_be_notified_that_taxon_with_this_code_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'code: Taxon with given code already exists.');
    }
    #[Then('there should still be only one taxon with code :code')]
    public function there_should_still_be_only_one_taxon_with_code(string $code): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::TAXONS), 'code', $code), 1, sprintf('There should be only one taxon with code "%s"', $code));
    }
    #[Then('the product :product should no longer have a main taxon')]
    public function the_product_should_no_longer_have_a_main_taxon(Product_Interface $product): void
    {
        Assert::null($product->get_main_taxon());
    }
    private function update_translations(string $locale_code, string $field, ?string $value = null): void
    {
        $data['translations'][$locale_code] = [];
        if ($value !== null) {
            $data['translations'][$locale_code][$field] = $value;
        }
        $this->client->update_request_data($data);
    }
}