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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Calculator\Fixed_Discount_Price_Calculator;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Calculator\Percentage_Discount_Price_Calculator;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Checker\In_For_Product_Scope_Variant_Checker;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Checker\In_For_Taxons_Scope_Variant_Checker;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Checker\In_For_Variants_Scope_Variant_Checker;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Catalog_Promotion_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Catalog_Promotions_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('I am browsing catalog promotions')]
    #[When('I browse catalog promotions')]
    public function i_browse_catalog_promotions(): void
    {
        $this->client->index(Resources::CATALOG_PROMOTIONS);
    }
    #[When('I create a new catalog promotion with :code code and :name name')]
    public function i_create_a_new_catalog_promotion_with_code_and_name(string $code, string $name): void
    {
        $this->client->build_create_request(Resources::CATALOG_PROMOTIONS);
        $this->client->add_request_data('code', $code);
        $this->client->add_request_data('name', $name);
        $this->client->create();
    }
    #[When('I create a new catalog promotion with :code code and :name name and :priority priority')]
    public function i_create_a_new_catalog_promotion_with_code_and_name_and_priority(string $code, string $name, int $priority): void
    {
        $this->client->build_create_request(Resources::CATALOG_PROMOTIONS);
        $this->client->add_request_data('code', $code);
        $this->client->add_request_data('name', $name);
        $this->client->add_request_data('priority', $priority);
        $this->client->create();
    }
    #[When('I create a new catalog promotion without specifying its code and name')]
    public function i_create_a_new_catalog_promotion_without_specifying_its_code_and_name(): void
    {
        $this->client->build_create_request(Resources::CATALOG_PROMOTIONS);
        $this->client->create();
    }
    #[When('I want to create a new catalog promotion')]
    public function i_want_to_create_new_catalog_promotion(): void
    {
        $this->client->build_create_request(Resources::CATALOG_PROMOTIONS);
    }
    #[When('I specify its :field as :value')]
    #[When('I :field it :value')]
    public function i_specify_its_as(string $field, string $value): void
    {
        $this->client->add_request_data($field, $value);
    }
    #[When('I set its priority to :priority')]
    public function i_sets_its_priority_to(int $priority): void
    {
        $this->client->add_request_data('priority', $priority);
    }
    #[When('I specify its :field as :value in :localeCode')]
    public function i_specify_its_as_in(string $field, string $value, string $locale_code): void
    {
        $data['translations'][$locale_code][$field] = $value;
        $this->client->update_request_data($data);
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->client->update_request_data(['enabled' => false]);
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->client->update_request_data(['enabled' => true]);
    }
    #[When('I describe it as :description in :localeCode')]
    public function i_describe_it_as_in(string $description, string $locale_code): void
    {
        $data = ['translations' => [$locale_code => []]];
        $data['translations'][$locale_code]['description'] = $description;
        $this->client->update_request_data($data);
    }
    #[When('I make it available in channel :channel')]
    public function i_make_it_available_in_channel(Channel_Interface $channel): void
    {
        $this->client->add_request_data('channels', [$this->iri_converter->get_iri_from_resource_in_section($channel, 'admin')]);
    }
    #[When('/^I make (it) unavailable in (channel "[^"]+")$/')]
    public function i_make_it_unavailable_in_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        $channels = $this->response_checker->get_value($this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code()), 'channels');
        foreach (array_keys($channels, $this->iri_converter->get_iri_from_resource_in_section($channel, 'admin')) as $key) {
            unset($channels[$key]);
        }
        $this->client->add_request_data('channels', $channels);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I rename the :catalogPromotion catalog promotion to :name')]
    #[When('I try to rename the :catalogPromotion catalog promotion to :name')]
    public function i_rename_the_catalog_promotion_to(Catalog_Promotion_Interface $catalog_promotion, string $name): void
    {
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        $this->client->update_request_data(['name' => $name]);
        $this->client->update();
    }
    #[When('I want to modify a catalog promotion :catalogPromotion')]
    #[When('I modify a catalog promotion :catalogPromotion')]
    public function i_want_to_modify_a_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[When('/^I add action that gives ("[^"]+") percentage discount$/')]
    public function i_add_action_that_gives_percentage_discount(float $amount): void
    {
        $actions = [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $amount]]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I add action that gives ("[^"]+") of fixed discount in the ("[^"]+" channel)$/')]
    public function i_add_action_that_gives_fixed_discount(int $amount, Channel_Interface $channel): void
    {
        $actions = [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $amount]]]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I add another action that gives ("[^"]+") percentage discount$/')]
    public function i_add_another_action_that_gives_percentage_discount(float $amount): void
    {
        $actions = $this->client->get_content()['actions'];
        $additional_action = [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $amount]]];
        $actions = array_merge_recursive($actions, $additional_action);
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I add another scope that applies on ("[^"]+" variant)$/')]
    public function i_add_another_scope_that_gives_percentage_discount(Product_Variant_Interface $product_variant): void
    {
        $scopes = $this->client->get_content()['scopes'];
        $additional_scope = [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$product_variant->get_code()]]]];
        $scopes = array_merge_recursive($scopes, $additional_scope);
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('/^I edit its action so that it reduces price by ("[^"]+")$/')]
    public function i_edit_its_action_so_that_it_reduces_price_by(float $amount): void
    {
        $content = $this->client->get_content();
        $content['actions'][0]['configuration']['amount'] = $amount;
        $this->client->update_request_data($content);
    }
    #[When('I remove its last action')]
    public function i_remove_its_last_action(): void
    {
        $content = $this->client->get_content();
        $last_key = array_key_last($content['actions']);
        if (null !== $last_key) {
            unset($content['actions'][$last_key]);
            $this->client->set_request_data($content);
        }
    }
    #[When('I add invalid percentage discount action with non number in amount')]
    public function i_add_invalid_percentage_discount_action_with_non_number_in_amount(): void
    {
        $actions = [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => 'text']]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('I make it start at :startDate and ends at :endDate')]
    public function i_make_catalog_promotion_operate_between_dates(string $start_date, string $end_date): void
    {
        $this->client->update_request_data(['startDate' => $start_date, 'endDate' => $end_date]);
    }
    #[When('I make it start yesterday and ends tomorrow')]
    public function i_make_catalog_promotion_operate_between_yesterday_and_tomorrow(): void
    {
        $this->client->update_request_data(['startDate' => (new \DateTime('yesterday'))->format('Y-m-d H:i:s'), 'endDate' => (new \DateTime('tomorrow'))->format('Y-m-d H:i:s')]);
    }
    #[When('I make it start at :startDate')]
    public function i_make_catalog_promotion_operate_from(string $start_date): void
    {
        $this->client->update_request_data(['startDate' => $start_date]);
    }
    #[When('/^I add scope that applies on ("[^"]+" variant) and ("[^"]+" variant)$/')]
    #[When('/^I add scope that applies on variants ("[^"]+" variant) and ("[^"]+" variant)$/')]
    public function i_add_scope_that_applies_on_variants(Product_Variant_Interface $first_variant, Product_Variant_Interface $second_variant): void
    {
        $scopes = [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$first_variant->get_code(), $second_variant->get_code()]]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add catalog promotion scope for taxon without taxons')]
    public function i_add_catalog_promotion_scope_for_taxon_without_taxons(): void
    {
        $scopes = [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => []]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add catalog promotion scope for taxon with nonexistent taxons')]
    public function i_add_catalog_promotion_scope_for_taxon_with_nonexistent_taxons(): void
    {
        $scopes = [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => ['BAD_TAXON', 'EVEN_WORSE_TAXON']]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add catalog promotion scope for product without products')]
    public function i_add_catalog_promotion_scope_for_product_without_products(): void
    {
        $scopes = [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => []]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add catalog promotion scope for product with nonexistent products')]
    public function i_add_catalog_promotion_scope_for_products_with_nonexistent_products(): void
    {
        $scopes = [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => ['BAD_PRODUCT', 'EVEN_WORSE_PRODUCT']]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add scope that applies on :taxon taxon')]
    public function i_add_scope_that_applies_on_taxon(Taxon_Interface $taxon): void
    {
        $scopes = [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => [$taxon->get_code()]]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add scope that applies on :product product')]
    public function i_add_scope_that_applies_on_product(Product_Interface $product): void
    {
        $scopes = [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('/^I create an exclusive "([^"]+)" catalog promotion with ([^"]+) priority that applies on ("[^"]+" product) and reduces price by ("[^"]+") in ("[^"]+" channel)$/')]
    public function i_create_an_exclusive_catalog_promotion_with_code_and_name_and_priority_that_applies_on_product_and_reduces_price_by_in_channel(string $name, int $priority, Product_Interface $product, float $discount, Channel_Interface $channel): void
    {
        $this->create_catalog_promotion($name, $priority, true, $product, $discount, $channel);
    }
    #[When('/^I create a "([^"]+)" catalog promotion with ([^"]+) priority that applies on ("[^"]+" product) and reduces price by ("[^"]+") in ("[^"]+" channel)$/')]
    public function i_create_a_catalog_promotion_with_code_and_name_and_priority_that_applies_on_product_and_reduces_price_by_in_channel(string $name, int $priority, Product_Interface $product, float $discount, Channel_Interface $channel): void
    {
        $this->create_catalog_promotion($name, $priority, false, $product, $discount, $channel);
    }
    #[When('I remove its last scope')]
    public function i_remove_its_last_scope(): void
    {
        $content = $this->client->get_content();
        $last_key = array_key_last($content['scopes']);
        if (null !== $last_key) {
            unset($content['scopes'][$last_key]);
            $this->client->set_request_data($content);
        }
    }
    #[When('I add :productVariant variant to its scope')]
    public function i_add_variant_to_its_scope(Product_Variant_Interface $product_variant): void
    {
        $content = $this->client->get_content();
        $content['scopes'][0]['configuration']['variants'][] = $product_variant->get_code();
        $this->client->set_request_data($content);
    }
    #[When('I remove :productVariant variant from its scope')]
    public function i_remove_variant_from_its_scope(Product_Variant_Interface $product_variant): void
    {
        $content = $this->client->get_content();
        $key = array_search($product_variant->get_code(), $content['scopes'][0]['configuration']['variants'] ?? []);
        if (false !== $key) {
            unset($content['scopes'][0]['configuration']['variants'][$key]);
            $this->client->set_request_data($content);
        }
    }
    #[When('/^I edit ("[^"]+" catalog promotion) to be applied on ("[^"]+" variant)$/')]
    public function i_edit_catalog_promotion_to_be_applied_on_variant(Catalog_Promotion_Interface $catalog_promotion, Product_Variant_Interface $product_variant): void
    {
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        $content = $this->client->get_content();
        unset($content['scopes'][0]);
        $content['scopes'][0]['type'] = $type;
        $content['scopes'][0]['configuration'] = $configuration;
        $this->client->set_request_data($content);
        $this->client->update();
        $this->change_first_scope_configuration_to($catalog_promotion, In_For_Variants_Scope_Variant_Checker::TYPE, ['variants' => [$product_variant->get_code()]]);
    }
    #[When('I disable :catalogPromotion catalog promotion')]
    public function i_disable_this_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->toggle_catalog_promotion($catalog_promotion, false);
    }
    #[When('I enable :catalogPromotion catalog promotion')]
    public function i_enable_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->toggle_catalog_promotion($catalog_promotion, true);
    }
    #[When('/^I edit it to have ("[^"]+") of fixed discount in the ("[^"]+" channel)$/')]
    public function i_edit_it_to_have_fixed_discount_in_the_channel(int $amount, Channel_Interface $channel): void
    {
        $content = $this->client->get_content();
        $content['actions'] = [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $amount]]]];
        $this->client->set_request_data($content);
    }
    #[When('I edit it to have empty amount of percentage discount')]
    public function i_edit_it_to_have_empty_percentage_discount(): void
    {
        $content = $this->client->get_content();
        $content['actions'] = [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => null]]];
        $this->client->set_request_data($content);
    }
    #[When('I edit it to have empty amount of fixed discount in the :channel channel')]
    public function i_edit_it_to_have_empty_fixed_discount_in_the_channel(Channel_Interface $channel): void
    {
        $content = $this->client->get_content();
        $content['actions'] = [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => null]]]];
        $this->client->set_request_data($content);
    }
    #[When('I add catalog promotion scope with nonexistent type')]
    public function i_add_catalog_promotion_scope_with_nonexistent_type(): void
    {
        $scopes = [['type' => 'nonexistent_scope', 'configuration' => ['config' => 'config']]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add for variants scope with the wrong configuration')]
    public function i_add_for_variants_scope_with_the_wrong_configuration(): void
    {
        $scopes = [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => ['wrong_code']]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add for variants scope without variants configured')]
    public function i_add_for_variants_scope_without_variants_configured(): void
    {
        $scopes = [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => []]]];
        $this->client->add_request_data('scopes', $scopes);
    }
    #[When('I add percentage discount action without amount configured')]
    public function i_add_percentage_discount_action_without_amount_configured(): void
    {
        $actions = [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => null]]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('I add fixed discount action without amount configured for the :channel channel')]
    public function i_add_fixed_discount_action_without_amount_configured(Channel_Interface $channel): void
    {
        $actions = [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => null]]]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('I add invalid fixed discount action with non number in amount for the :channel channel')]
    public function i_add_invalid_fixed_discount_action_with_non_number_in_amount_for_the_channel(Channel_Interface $channel): void
    {
        $actions = [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => 'wrong value']]]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('I add invalid fixed discount action configured for nonexistent channel')]
    public function i_add_invalid_fixed_discount_action_configured_for_nonexistent_channel(): void
    {
        $actions = [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => ['nonexistent_channel' => ['amount' => 1000]]]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('I add catalog promotion action with nonexistent type')]
    public function i_add_catalog_promotion_action_with_nonexistent_type(): void
    {
        $actions = [['type' => 'nonexistent_action', 'configuration' => []]];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I make (this catalog promotion) unavailable in the ("[^"]+" channel)$/')]
    #[When('/^I make the ("[^"]+" catalog promotion) unavailable in the ("[^"]+" channel)$/')]
    public function i_make_this_catalog_promotion_unavailable_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        $catalog_promotion_code = $catalog_promotion->get_code();
        Assert::not_null($catalog_promotion_code);
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion_code);
        $content = $this->client->get_content();
        foreach (array_keys($content['channels'], $this->iri_converter->get_iri_from_resource($channel)) as $key) {
            unset($content['channels'][$key]);
        }
        $this->client->set_request_data($content);
        $this->client->update();
    }
    #[When('/^I make (this catalog promotion) available in the ("[^"]+" channel)$/')]
    #[When('/^I make ("[^"]+" catalog promotion) available in the ("[^"]+" channel)$/')]
    public function i_make_this_catalog_promotion_available_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        $content = $this->client->get_content();
        $content['channels'][] = $this->iri_converter->get_iri_from_resource($channel);
        $this->client->update_request_data(['channels' => $content['channels']]);
        $this->client->update();
    }
    #[When('/^I switch (this catalog promotion) availability from the ("[^"]+" channel) to the ("[^"]+" channel)$/')]
    #[When('/^I switch ("[^"]+" catalog promotion) availability from the ("[^"]+" channel) to the ("[^"]+" channel)$/')]
    public function i_switch_this_catalog_promotion_availability_from_the_channel_to_the_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $removed_channel, Channel_Interface $added_channel): void
    {
        $catalog_promotion_code = $catalog_promotion->get_code();
        Assert::not_null($catalog_promotion_code);
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion_code);
        $content = $this->client->get_content();
        foreach (array_keys($content['channels'], $this->iri_converter->get_iri_from_resource($removed_channel)) as $key) {
            unset($content['channels'][$key]);
        }
        $content['channels'][] = $this->iri_converter->get_iri_from_resource($added_channel);
        $this->client->set_request_data($content);
        $this->client->update();
    }
    #[When('I view details of the catalog promotion :catalogPromotion')]
    public function i_view_details_of_the_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[When('I( try to) change its end date to :endDate')]
    public function i_change_its_end_date_to(string $end_date): void
    {
        $this->client->update_request_data(['endDate' => $end_date]);
    }
    #[When('/^I search by "([^"]+)" (code|name)$/')]
    public function i_search_by_name(string $phrase, string $field): void
    {
        $this->client->add_filter($field, $phrase);
        $this->client->filter();
    }
    #[When('I filter by :channel channel')]
    public function i_filter_by_channel(Channel_Interface $channel): void
    {
        $this->client->add_filter('channel', $this->iri_converter->get_iri_from_resource($channel));
        $this->client->filter();
    }
    #[When('I filter enabled catalog promotions')]
    public function i_filter_enabled_catalog_promotions(): void
    {
        $this->client->add_filter('enabled', true);
        $this->client->filter();
    }
    #[When('/^I filter by (active|failed|inactive|processing) state$/')]
    public function i_filter_by_state(string $state): void
    {
        $this->client->add_filter('state', $state);
        $this->client->filter();
    }
    #[When('/^I filter by (end|start) date up to "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_date_up_to(string $date_type, string $date): void
    {
        $this->client->add_filter(sprintf('%sDate[before]', $date_type), $date);
        $this->client->filter();
    }
    #[When('/^I filter by (end|start) date from "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_by_date_from(string $date_type, string $date): void
    {
        $this->client->add_filter(sprintf('%sDate[after]', $date_type), $date);
        $this->client->filter();
    }
    #[When('/^I filter by (end|start) date from "(\d{4}-\d{2}-\d{2})" up to "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_by_date_from_date_to_date(string $date_type, string $from_date, string $to_date): void
    {
        $this->client->add_filter(sprintf('%sDate[after]', $date_type), $from_date);
        $this->client->add_filter(sprintf('%sDate[before]', $date_type), $to_date);
        $this->client->filter();
    }
    #[When('I sort catalog promotions by :order :field')]
    public function i_sort_catalog_promotion_by_order_field(string $order, string $field): void
    {
        $this->client->add_filter(sprintf('order[%s]', lcfirst(str_replace(' ', '', ucwords($field)))), $order === 'descending' ? 'desc' : 'asc');
        $this->client->filter();
    }
    #[When('I request the removal of :catalogPromotion catalog promotion')]
    public function i_request_the_removal_of_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->client->delete(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
    }
    #[Then('I should be notified that the removal operation has started successfully')]
    public function i_should_be_notified_that_the_removal_operation_has_started_successfully(): void
    {
        Assert::true($this->response_checker->is_accepted($this->client->get_last_response()), 'Removal operation has not started successfully');
    }
    #[Then('there should be :amount new catalog promotion on the list')]
    #[Then('there should be :amount catalog promotions on the list')]
    #[Then('there should be an empty list of catalog promotions')]
    public function there_should_be_new_catalog_promotion_on_the_list(int $amount = 0): void
    {
        Assert::count($this->response_checker->get_collection($this->client->index(Resources::CATALOG_PROMOTIONS)), $amount);
    }
    #[Then('/^it should have ("[^"]+") discount$/')]
    public function it_should_have_discount(float $amount): void
    {
        $catalog_promotion = $this->response_checker->get_collection($this->client->get_last_response())[0];
        Assert::same($catalog_promotion['actions'][0]['configuration']['amount'], $amount);
    }
    #[Then('/^the ("[^"]+" catalog promotion) should have ("[^"]+") of fixed discount in the ("[^"]+" channel)$/')]
    public function the_catalog_promotion_should_have_fixed_discount_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, int $amount, Channel_Interface $channel): void
    {
        $catalog_promotion = $this->response_checker->get_collection($this->client->get_last_response())[0];
        Assert::same($catalog_promotion['actions'][0]['configuration'][$channel->get_code()]['amount'], $amount);
    }
    #[Then('/^this catalog promotion should have ("[^"]+") of fixed discount in the ("[^"]+" channel)$/')]
    #[Then('/^it should reduce price by ("[^"]+") in the ("[^"]+" channel)$/')]
    public function this_catalog_promotion_should_have_fixed_discount_in_the_channel(int $amount, Channel_Interface $channel): void
    {
        $catalog_promotion_actions = $this->response_checker->get_value($this->client->get_last_response(), 'actions');
        foreach ($catalog_promotion_actions as $catalog_promotion_action) {
            if ($catalog_promotion_action['type'] === Fixed_Discount_Price_Calculator::TYPE && $catalog_promotion_action['configuration'][$channel->get_code()]['amount'] === $amount) {
                return;
            }
        }
        throw new \Exception(sprintf('There is no "%s" action with %d for "%s" channel', Fixed_Discount_Price_Calculator::TYPE, $amount, $channel->get_name()));
    }
    #[Then('/^this catalog promotion should have ("[^"]+") percentage discount$/')]
    #[Then('/^it should reduce price by ("[^"]+")$/')]
    public function this_catalog_promotion_should_have_percentage_discount(float $amount): void
    {
        $catalog_promotion_actions = $this->response_checker->get_response_content($this->client->get_last_response())['actions'];
        foreach ($catalog_promotion_actions as $catalog_promotion_action) {
            if ($catalog_promotion_action['configuration']['amount'] === $amount && $catalog_promotion_action['type'] === Percentage_Discount_Price_Calculator::TYPE) {
                return;
            }
        }
        throw new \Exception(sprintf('There is no "%s" action with %f', Percentage_Discount_Price_Calculator::TYPE, $amount));
    }
    #[Then('it should have :code code and :name name')]
    public function it_should_have_code_and_name(string $code, string $name): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->index(Resources::CATALOG_PROMOTIONS), ['code' => $code, 'name' => $name]), sprintf('Cannot find catalog promotions with code "%s" and name "%s" in the list', $code, $name));
    }
    #[Then('it should have priority equal to :priority')]
    public function it_should_have_priority_equal_to(int $priority): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->index(Resources::CATALOG_PROMOTIONS), ['priority' => $priority]), sprintf('Cannot find catalog promotions with priority "%d"', $priority));
    }
    #[Then('the catalog promotions named :firstName and :secondName should be in the registry')]
    #[Then('the catalog promotion named :firstName should be in the registry')]
    public function the_catalog_promotions_named_should_be_in_the_registry(string ...$names): void
    {
        foreach ($names as $name) {
            Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CATALOG_PROMOTIONS), 'name', $name), sprintf('Cannot find catalog promotions with name "%s" in the list', $name));
        }
    }
    #[Then('the catalog promotion named :catalogPromotion should operate between :startDate and :endDate')]
    #[Then('/^(it) should operate between "([^"]+)" and "([^"]+)"$/')]
    #[Then('/^(it) should start at "([^"]+)" and end at "([^"]+)"$/')]
    #[Then('/^(this catalog promotion) should operate between "([^"]+)" and "([^"]+)"$/')]
    public function the_catalog_promotion_named_should_operate_between_dates(Catalog_Promotion_Interface $catalog_promotion, string $start_date, string $end_date): void
    {
        $response = $this->client->index(Resources::CATALOG_PROMOTIONS);
        Assert::true($this->response_checker->has_item_with_values($response, ['name' => $catalog_promotion->get_name(), 'startDate' => $start_date . ':00', 'endDate' => $end_date . ':00']), sprintf('Cannot find catalog promotions with name "%s" operating between "%s" and "%s" in the list', $catalog_promotion->get_name(), $start_date, $end_date));
    }
    #[Then('the catalog promotion named :catalogPromotion should have priority :priority')]
    public function the_catalog_promotion_named_should_have_priority(Catalog_Promotion_Interface $catalog_promotion, int $priority): void
    {
        $response = $this->client->index(Resources::CATALOG_PROMOTIONS);
        Assert::true($this->response_checker->has_item_with_values($response, ['name' => $catalog_promotion->get_name(), 'priority' => $priority]), sprintf('Cannot find catalog promotions with name "%s" and priority "%s" in the list', $catalog_promotion->get_name(), $priority));
    }
    #[Then('/^(it) should operate between yesterday and tomorrow$/')]
    public function the_catalog_promotions_named_should_operate_between_yesterday_and_tomorrow(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $response = $this->client->index(Resources::CATALOG_PROMOTIONS);
        Assert::true($this->response_checker->has_item_with_values($response, ['name' => $catalog_promotion->get_name(), 'startDate' => (new \DateTime('yesterday'))->format('Y-m-d H:i:s'), 'endDate' => (new \DateTime('tomorrow'))->format('Y-m-d H:i:s')]), sprintf('Cannot find catalog promotions with name "%s" operating between "%s" and "%s" in the list', $catalog_promotion->get_name(), (new \DateTime('yesterday'))->format('Y-m-d H:i:s'), (new \DateTime('tomorrow'))->format('Y-m-d H:i:s')));
    }
    #[Then('/^(it) should be (inactive|active)$/')]
    public function it_should_be(Catalog_Promotion_Interface $catalog_promotion, string $state): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['name' => $catalog_promotion->get_name(), 'state' => $state]));
    }
    #[Then('/^(its) priority should be ([^"]+)$/')]
    public function it_priority_should_be(Catalog_Promotion_Interface $catalog_promotion, int $priority): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['priority' => $priority]));
    }
    #[Then('/^(this catalog promotion) should(?:| still) be (inactive|active)$/')]
    public function this_catalog_promotion_should_be(Catalog_Promotion_Interface $catalog_promotion, string $state): void
    {
        $response = $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::true($this->response_checker->has_value($response, 'state', $state));
    }
    #[Then('/^("[^"]+" catalog promotion) should apply to ("[^"]+" variant) and ("[^"]+" variant)$/')]
    public function catalog_promotion_should_apply_to_variants(Catalog_Promotion_Interface $catalog_promotion, Product_Variant_Interface $first_variant, Product_Variant_Interface $second_variant): void
    {
        Assert::same(['variants' => [$first_variant->get_code(), $second_variant->get_code()]], $this->response_checker->get_collection($this->client->get_last_response())[0]['scopes'][0]['configuration']);
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[Then(':catalogPromotionName catalog promotion should apply to all products from :taxon taxon')]
    public function catalog_promotion_should_apply_to_all_products_from_taxons(string $catalog_promotion_name, Taxon_Interface $taxon): void
    {
        Assert::same(['taxons' => [$taxon->get_code()]], $this->response_checker->get_collection($this->client->get_last_response())[0]['scopes'][0]['configuration']);
    }
    #[Then('the :catalogPromotionName catalog promotion should apply to all variants of :product product')]
    public function the_catalog_promotion_should_apply_to_all_variants_of_product(string $catalog_promotion_name, Product_Interface $product): void
    {
        Assert::same(['products' => [$product->get_code()]], $this->response_checker->get_collection($this->client->get_last_response())[0]['scopes'][0]['configuration']);
    }
    #[Then('the catalog promotion :catalogPromotion should be available in channel :channel')]
    #[Then('/^(this catalog promotion) should be available in (channel "[^"]+")$/')]
    public function it_should_be_available_in_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        Assert::true($this->response_checker->has_value_in_collection($this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code()), 'channels', $this->iri_converter->get_iri_from_resource_in_section($channel, 'admin')), sprintf('Catalog promotion is not assigned to %s channel', $channel->get_name()));
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[Then('/^(this catalog promotion) should not be available in (channel "[^"]+")$/')]
    public function it_should_not_be_available_in_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        Assert::false($this->response_checker->has_value_in_collection($this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code()), 'channels', $this->iri_converter->get_iri_from_resource_in_section($channel, 'admin')), sprintf('Catalog promotion is assigned to %s channel', $channel->get_name()));
    }
    #[Then('I should be notified that catalog promotion has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Catalog promotion could not be created');
    }
    #[Then('I should be notified that not all channels are filled')]
    public function i_should_be_notified_that_not_all_channels_are_filled(): void
    {
        $response = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same($response['violations'][0]['message'], 'This field is missing.');
    }
    #[Then('/^(this catalog promotion) name should(?:| still) be "([^"]+)"$/')]
    public function this_catalog_promotion_name_should_be(Catalog_Promotion_Interface $catalog_promotion, string $name): void
    {
        $response = $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::true($this->response_checker->has_value($response, 'name', $name), sprintf('Catalog promotion\'s name %s does not exist', $name));
    }
    #[Then('/^(this catalog promotion) should be (labelled|described) as "([^"]+)" in ("[^"]+" locale)$/')]
    public function this_catalog_promotion_label_in_locale_should_be(Catalog_Promotion_Interface $catalog_promotion, string $field, string $value, string $locale_code): void
    {
        $fields_mapping = ['labelled' => 'label', 'described' => 'description'];
        $response = $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::true($this->response_checker->has_translation($response, $locale_code, $fields_mapping[$field], $value));
    }
    #[Then('/^(this catalog promotion) should be applied on ("[^"]+" variant)$/')]
    public function this_catalog_promotion_should_be_applied_on_variant(Catalog_Promotion_Interface $catalog_promotion, Product_Variant_Interface $product_variant): void
    {
        $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::true($this->catalog_promotion_has_values_in_scope_configuration('variants', $product_variant->get_code()));
    }
    #[Then('it should apply on :variant variant')]
    public function it_should_apply_on_variant(Product_Variant_Interface $variant): void
    {
        Assert::true($this->catalog_promotion_has_values_in_scope_configuration('variants', $variant->get_code()));
    }
    #[Then('it should apply on :product product')]
    public function it_should_apply_on_product(Product_Interface $product): void
    {
        Assert::true($this->catalog_promotion_has_values_in_scope_configuration('products', $product->get_code()));
    }
    #[Then('/^(this catalog promotion) should be applied on ("[^"]+" taxon)$/')]
    public function this_catalog_promotion_should_be_applied_on_taxon(Catalog_Promotion_Interface $catalog_promotion, Taxon_Interface $taxon): void
    {
        $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::true($this->catalog_promotion_has_values_in_scope_configuration('taxons', $taxon->get_code()));
    }
    #[Then('/^(this catalog promotion) should not be applied on ("[^"]+" variant)$/')]
    public function this_catalog_promotion_should_not_be_applied_on(Catalog_Promotion_Interface $catalog_promotion, Product_Variant_Interface $product_variant): void
    {
        $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::false($this->catalog_promotion_has_values_in_scope_configuration('variants', $product_variant->get_code()));
    }
    #[Then('/^(this catalog promotion) should be applied on ("[^"]+" product)$/')]
    public function this_catalog_promotion_should_be_applied_on_product(Catalog_Promotion_Interface $catalog_promotion, Product_Interface $product): void
    {
        $this->client->show(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        Assert::true($this->catalog_promotion_has_values_in_scope_configuration('products', $product->get_code()));
    }
    #[Then('I should be notified that code and name are required')]
    public function i_should_be_notified_that_code_and_name_are_required(): void
    {
        $validation_error = $this->response_checker->get_error($this->client->get_last_response());
        Assert::contains($validation_error, 'code: Please enter catalog promotion code.');
        Assert::contains($validation_error, 'name: Please enter catalog promotion name.');
    }
    #[Then('I should be notified that catalog promotion with this code already exists')]
    public function i_should_be_notified_that_catalog_promotion_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Catalog promotion has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: The catalog promotion with given code already exists.');
    }
    #[Then('there should still be only one catalog promotion with code :code')]
    public function there_should_still_be_only_one_catalog_promotion_with_code(string $code): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::CATALOG_PROMOTIONS), 'code', $code), 1);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        $this->client->update();
        Assert::false($this->response_checker->has_value($this->client->get_last_response(), 'code', 'NEW_CODE'), 'The code has been changed, but it should not');
    }
    #[Then('I should be notified that a discount amount is required')]
    public function i_should_be_notified_that_a_discount_amount_is_required(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Catalog promotion has been created successfully, but it should not');
        Assert::contains($this->response_checker->get_error($response), 'The percentage discount amount must be configured.');
    }
    #[Then('/^I should be notified that type of action is invalid$/')]
    public function i_should_be_notified_that_type_of_action_is_invalid(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Catalog promotion action type is invalid. Available types are fixed_discount, percentage_discount.');
    }
    #[Then('/^I should be notified that type of scope is invalid$/')]
    public function i_should_be_notified_that_type_of_scope_is_invalid(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Catalog promotion scope type is invalid. Available types are for_products, for_taxons, for_variants.');
    }
    #[Then('I should be notified that a discount amount should be between 0% and 100%')]
    public function i_should_be_notified_that_a_discount_amount_should_be_between0and100(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The percentage discount amount must be between 0% and 100%.');
    }
    #[Then('I should be notified that the percentage amount should be a number and cannot be empty')]
    public function i_should_be_notified_that_discount_amount_should_be_a_number_and_cannot_be_empty(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The percentage discount amount must be a number and can not be empty.');
    }
    #[Then('I should be notified that the fixed amount should be a number')]
    #[Then('I should be notified that the fixed amount cannot be empty')]
    public function i_should_be_notified_that_the_fixed_amount_should_be_a_number(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Provided configuration contains errors. Please add the fixed discount amount that is a number greater than 0.');
    }
    #[Then('I should be notified that at least one of the provided channel codes does not exist')]
    public function i_should_be_notified_that_at_least_one_of_the_provided_channel_codes_does_not_exist(): void
    {
        Assert::regex($this->response_checker->get_error($this->client->get_last_response()), '/Channel with code [^"]+ does not exist/');
    }
    #[Then('I should be notified that scope configuration is invalid')]
    public function i_should_be_notified_that_scope_configuration_is_invalid(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Product variant with code wrong_code does not exist.');
    }
    #[Then('/^I should be notified that I must add at least one (product|taxon)$/')]
    public function i_should_be_notified_that_i_must_add_at_least_one(string $entity): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Provided configuration contains errors. Please add at least 1 %s.', $entity));
    }
    #[Then('/^I should be notified that I can add only existing (product|taxon)$/')]
    public function i_should_be_notified_that_i_can_add_only_existing(string $entity): void
    {
        Assert::regex($this->response_checker->get_error($this->client->get_last_response()), sprintf('/%s with code [^"]+ does not exist.$/', ucfirst($entity)));
    }
    #[Then('I should be notified that at least 1 variant is required')]
    public function i_should_be_notified_that_at_least1variant_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Please add at least 1 variant.');
    }
    #[Then('I should not be able to edit it due to wrong state')]
    public function i_should_not_be_able_to_edit_it_due_to_wrong_state(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The catalog promotion cannot be edited as it is currently being processed.');
    }
    #[Then('its name should be :name')]
    public function its_name_should_be(string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'name', $name));
    }
    #[Given('it should be exclusive')]
    public function it_should_be_exclusive(): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'exclusive', true));
    }
    #[Given('it should not be exclusive')]
    public function it_should_not_be_exclusive(): void
    {
        Assert::false($this->response_checker->has_value($this->client->get_last_response(), 'exclusive', true));
    }
    #[Then('I should get information that the end date cannot be set before start date')]
    public function i_should_get_information_that_the_end_date_cannot_be_set_before_start_date(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'endDate: End date cannot be set before start date.');
    }
    #[Then('I should see a catalog promotion with name :name')]
    public function i_should_see_a_catalog_promotion_with_name(string $name): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->has_item_with_value($response, 'name', $name), sprintf('No catalog promotion with name "%s" has been found.', $name));
    }
    #[Then('I should not see a catalog promotion with name :name')]
    public function i_should_not_see_a_catalog_promotion_with_name(string $name): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->has_item_with_value($response, 'name', $name), sprintf('Catalog promotion with name "%s" has been found, but should not.', $name));
    }
    #[Then('I should see :count catalog promotions on the list')]
    public function i_should_see_count_catalog_promotions_on_the_list(int $count): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), $count);
    }
    #[Then('the first catalog promotion should have code :code')]
    public function the_first_catalog_promotion_should_have_code(string $code): void
    {
        $catalog_promotions = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(reset($catalog_promotions)['code'], $code);
    }
    private function catalog_promotion_has_values_in_scope_configuration(string $configuration_key, string ...$values): bool
    {
        $response = $this->response_checker->get_response_content($this->client->get_last_response());
        $configuration = $response['scopes'] ?? [];
        if ([] === $configuration || empty($configuration[0])) {
            return false;
        }
        foreach ($configuration as $scope) {
            if (!isset($scope['configuration'][$configuration_key])) {
                continue;
            }
            foreach ($values as $value) {
                if (in_array($value, $scope['configuration'][$configuration_key], true)) {
                    return true;
                }
            }
        }
        return false;
    }
    private function toggle_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion, bool $enabled): void
    {
        $this->client->build_update_request(Resources::CATALOG_PROMOTIONS, $catalog_promotion->get_code());
        $this->client->update_request_data(['enabled' => $enabled]);
        $this->client->update();
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    private function create_catalog_promotion(string $name, int $priority, bool $exclusive, Product_Interface $product, float $discount, Channel_Interface $channel): void
    {
        $this->client->build_create_request(Resources::CATALOG_PROMOTIONS);
        $this->client->update_request_data(['code' => String_Inflector::name_to_code($name), 'name' => $name, 'priority' => $priority, 'enabled' => true, 'channels' => [$this->iri_converter->get_iri_from_resource($channel)], 'exclusive' => $exclusive, 'translations' => ['en_US' => ['label' => $name]], 'actions' => [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], 'scopes' => [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]]]);
        $this->client->create();
    }
}