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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Context\Ui\Admin\Helper\Validation_Trait;
use Sylius\Behat\Element\Admin\Catalog_Promotion\Filter_Element_Interface;
use Sylius\Behat\Element\Admin\Catalog_Promotion\Form_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Catalog_Promotion\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Catalog_Promotion\Show_Page_Interface;
use Sylius\Behat\Page\Admin\Catalog_Promotion\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
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
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Show_Page_Interface $show_page, private Form_Element_Interface $form_element, private Filter_Element_Interface $filter_element, private Shared_Storage_Interface $shared_storage, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('I am browsing catalog promotions')]
    #[When('I browse catalog promotions')]
    public function i_browse_catalog_promotions(): void
    {
        $this->index_page->open();
    }
    #[When('I want to create a new catalog promotion')]
    public function i_want_to_create_new_catalog_promotion(): void
    {
        $this->create_page->open();
    }
    #[When('I create a new catalog promotion with :code code and :name name')]
    public function i_create_a_new_catalog_promotion_with_code_and_name(string $code, string $name): void
    {
        $this->create_page->open();
        $this->create_page->specify_code($code);
        $this->form_element->name_it($name);
        $this->create_page->create();
    }
    #[When('I create a new catalog promotion with :code code and :name name and :priority priority')]
    public function i_create_a_new_catalog_promotion_with_code_and_name_and_priority(string $code, string $name, int $priority): void
    {
        $this->create_page->open();
        $this->create_page->specify_code($code);
        $this->form_element->name_it($name);
        $this->form_element->prioritize_it($priority);
        $this->create_page->create();
    }
    #[When('I create a new catalog promotion without specifying its code and name')]
    public function i_create_a_new_catalog_promotion_without_specifying_its_code_and_name(): void
    {
        $this->create_page->open();
        $this->create_page->create();
    }
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('I name it :name')]
    public function i_name_it(string $name): void
    {
        $this->form_element->name_it($name);
    }
    #[When('I set its priority to :priority')]
    public function i_set_its_priority_to(int $priority): void
    {
        $this->form_element->prioritize_it($priority);
    }
    #[When('I specify its label as :label in :localeCode')]
    public function i_specify_its_label_as_in(string $label, string $locale_code): void
    {
        $this->form_element->label_it($label, $locale_code);
    }
    #[When('I describe it as :description in :localeCode')]
    public function i_describe_it_as_in(string $description, string $locale_code): void
    {
        $this->form_element->describe_it($description, $locale_code);
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->form_element->change_enable_to(true);
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->form_element->change_enable_to(false);
    }
    #[When('I make it available in channel :channelName')]
    public function i_make_it_available_in_channel(string $channel_name): void
    {
        $this->form_element->check_channel($channel_name);
    }
    #[When('I make it start at :startDate and ends at :endDate')]
    public function i_make_it_operate_between_dates(string $start_date, string $end_date): void
    {
        $this->form_element->specify_start_date(new \DateTime($start_date));
        $this->form_element->specify_end_date(new \DateTime($end_date));
    }
    #[When('I make it start yesterday and ends tomorrow')]
    public function i_make_it_operate_between_yesterday_and_tomorrow(): void
    {
        $this->form_element->specify_start_date(new \DateTime('yesterday'));
        $this->form_element->specify_end_date(new \DateTime('tomorrow'));
    }
    #[When('I make it start at :startDate')]
    public function i_make_it_operate_from_date(string $start_date): void
    {
        $this->form_element->specify_start_date(new \DateTime($start_date));
    }
    #[When('I make it unavailable in channel :channelName')]
    public function i_make_it_unavailable_in_channel(string $channel_name): void
    {
        $this->form_element->uncheck_channel($channel_name);
    }
    #[When('I( try to) change its end date to :endDate')]
    public function i_change_its_end_date_to(string $end_date): void
    {
        $this->form_element->specify_end_date(new \DateTime($end_date));
    }
    #[When('I add a new catalog promotion scope')]
    public function i_add_a_new_catalog_promotion_scope(): void
    {
        $this->form_element->add_scope(In_For_Product_Scope_Variant_Checker::TYPE);
    }
    #[When('/^I add(?:| another) scope that applies on ("[^"]+" variant)$/')]
    #[When('/^I add scope that applies on ("[^"]+" variant) and ("[^"]+" variant)$/')]
    #[When('/^I add scope that applies on variants ("[^"]+" variant) and ("[^"]+" variant)$/')]
    public function i_add_scope_that_applies_on_variants(Product_Variant_Interface ...$variants): void
    {
        $variant_names = array_map(fn(Product_Variant_Interface $variant): ?string => $variant->get_name(), $variants);
        $this->form_element->add_scope(In_For_Variants_Scope_Variant_Checker::TYPE);
        $this->form_element->select_scope_option($variant_names);
    }
    #[When('/^I add scope that applies on ("[^"]+" taxon)$/')]
    public function i_add_scope_that_applies_on_taxons(Taxon_Interface ...$taxons): void
    {
        $taxon_names = array_map(fn(Taxon_Interface $taxon): ?string => $taxon->get_name(), $taxons);
        $this->form_element->add_scope(In_For_Taxons_Scope_Variant_Checker::TYPE);
        $this->form_element->select_scope_option($taxon_names);
    }
    #[When('/^I add scope that applies on ("[^"]+" product)$/')]
    public function i_add_scope_that_applies_on_product(Product_Interface $product): void
    {
        $this->form_element->add_scope(In_For_Product_Scope_Variant_Checker::TYPE);
        $this->form_element->select_scope_option([$product->get_name()]);
    }
    #[When('I add :productVariant variant to its scope')]
    public function i_add_variant_to_its_scope(Product_Variant_Interface $product_variant): void
    {
        $this->form_element->select_scope_option([$product_variant->get_name()]);
    }
    #[When('I remove :productVariant variant from its scope')]
    public function i_remove_variant_from_its_scope(Product_Variant_Interface $product_variant): void
    {
        $this->form_element->remove_scope_option([$product_variant->get_name()]);
    }
    #[When('I remove its last action')]
    public function i_remove_its_last_action(): void
    {
        $this->form_element->remove_last_action();
    }
    #[When('I add a new catalog promotion action')]
    public function i_add_a_new_catalog_promotion_action(): void
    {
        $this->form_element->add_action(Fixed_Discount_Price_Calculator::TYPE);
    }
    #[When('I add another action that gives ":discount%" percentage discount')]
    #[When('I add action that gives ":discount%" percentage discount')]
    public function i_add_action_that_gives_percentage_discount(string $discount): void
    {
        $this->form_element->add_action(Percentage_Discount_Price_Calculator::TYPE);
        $this->form_element->fill_action_option('Amount', $discount);
    }
    #[When('/^I add action that gives "(?:€|£|\$)([^"]+)" of fixed discount in the ("[^"]+" channel)$/')]
    public function i_add_action_that_gives_fixed_discount(string $discount, Channel_Interface $channel): void
    {
        $this->form_element->add_action(Fixed_Discount_Price_Calculator::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Amount', $discount);
    }
    #[When('/^I create an exclusive "([^"]+)" catalog promotion with ([^"]+) priority that applies on ("[^"]+" product) and reduces price by "((?:\d+\.)?\d+)%" in ("[^"]+" channel)$/')]
    public function i_create_an_exclusive_catalog_promotion_with_code_and_priority_that_reduces_price_by_in_the_channel_and_applies_on_product(string $name, int $priority, Product_Interface $product, string $discount, string $channel): void
    {
        $this->create_catalog_promotion($name, $priority, true, $product, $discount, $channel);
    }
    #[When('/^I create a "([^"]+)" catalog promotion with ([^"]+) priority that applies on ("[^"]+" product) and reduces price by "((?:\d+\.)?\d+)%" in ("[^"]+" channel)$/')]
    public function i_create_a_catalog_promotion_with_code_and_name_and_priority_that_applies_on_product_and_reduces_price_by_in_channel(string $name, int $priority, Product_Interface $product, string $discount, string $channel): void
    {
        $this->create_catalog_promotion($name, $priority, false, $product, $discount, $channel);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I rename the :catalogPromotion catalog promotion to :name')]
    #[When('I try to rename the :catalogPromotion catalog promotion to :name')]
    public function i_rename_the_catalog_promotion_to(Catalog_Promotion_Interface $catalog_promotion, string $name): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->form_element->name_it($name);
        $this->update_page->save_changes();
    }
    #[When('I modify a catalog promotion :catalogPromotion')]
    #[When('I want to modify a catalog promotion :catalogPromotion')]
    public function i_want_to_modify_a_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
    }
    #[When('I remove its last scope')]
    public function i_remove_its_last_scope(): void
    {
        $this->form_element->remove_last_scope();
    }
    #[When('I disable :catalogPromotion catalog promotion')]
    public function i_disable_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->form_element->change_enable_to(false);
        $this->update_page->save_changes();
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[When('I enable :catalogPromotion catalog promotion')]
    public function i_enable_this_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->form_element->change_enable_to(true);
        $this->update_page->save_changes();
    }
    #[When('I edit its action so that it reduces price by ":discount%"')]
    public function i_edit_its_action_so_that_it_reduces_price_by(string $discount): void
    {
        $this->form_element->fill_action_option('Amount', $discount);
    }
    #[When('I add for variants scope without variants configured')]
    public function i_add_for_variants_scope_without_variants_configured(): void
    {
        $this->form_element->add_scope(In_For_Variants_Scope_Variant_Checker::TYPE);
    }
    #[When('I add catalog promotion scope for taxon without taxons')]
    public function i_add_for_taxon_scope_without_taxons_configured(): void
    {
        $this->form_element->add_scope(In_For_Taxons_Scope_Variant_Checker::TYPE);
    }
    #[When('I add catalog promotion scope for product without products')]
    public function i_add_catalog_promotion_scope_for_product_without_products(): void
    {
        $this->form_element->add_scope(In_For_Product_Scope_Variant_Checker::TYPE);
    }
    #[When('I add percentage discount action without amount configured')]
    public function i_add_percentage_discount_action_without_amount_configured(): void
    {
        $this->form_element->add_action(Percentage_Discount_Price_Calculator::TYPE);
    }
    #[When('I add fixed discount action without amount configured for the :channel channel')]
    public function i_add_fixed_discount_action_without_amount_configured(): void
    {
        $this->form_element->add_action(Fixed_Discount_Price_Calculator::TYPE);
    }
    #[When('I add invalid percentage discount action with non number in amount')]
    public function i_add_invalid_percentage_discount_action_with_non_number_in_amount(): void
    {
        $this->form_element->add_action(Percentage_Discount_Price_Calculator::TYPE);
        $this->form_element->fill_action_option('Amount', 'alot');
    }
    #[When('I add invalid fixed discount action with non number in amount for the :channel channel')]
    public function i_add_invalid_fixed_discount_action_with_non_number_in_amount_for_the_channel(Channel_Interface $channel): void
    {
        $this->form_element->add_action(Fixed_Discount_Price_Calculator::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Amount', 'wrong value');
    }
    #[When('/^I make (this catalog promotion) unavailable in the ("[^"]+" channel)$/')]
    #[When('/^I make the ("[^"]+" catalog promotion) unavailable in the ("[^"]+" channel)$/')]
    public function i_make_this_catalog_promotion_unavailable_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->form_element->uncheck_channel($channel->get_name());
        $this->update_page->save_changes();
    }
    #[When('/^I make (this catalog promotion) available in the ("[^"]+" channel)$/')]
    #[When('/^I make ("[^"]+" catalog promotion) available in the ("[^"]+" channel)$/')]
    public function i_make_this_catalog_promotion_available_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->form_element->check_channel($channel->get_name());
        $this->update_page->save_changes();
    }
    #[When('/^I switch (this catalog promotion) availability from the ("[^"]+" channel) to the ("[^"]+" channel)$/')]
    #[When('/^I switch ("[^"]+" catalog promotion) availability from the ("[^"]+" channel) to the ("[^"]+" channel)$/')]
    public function i_switch_this_catalog_promotion_availability_from_the_channel_to_the_channel(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $removed_channel, Channel_Interface $added_channel): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->form_element->uncheck_channel($removed_channel->get_name());
        $this->form_element->check_channel($added_channel->get_name());
        $this->update_page->save_changes();
    }
    #[When('I view details of the catalog promotion :catalogPromotion')]
    public function i_view_details_of_the_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->show_page->open(['id' => $catalog_promotion->get_id()]);
    }
    #[When('I edit it to have empty amount of percentage discount')]
    public function i_edit_it_to_have_empty_percentage_discount(): void
    {
        $this->form_element->fill_action_option('Amount', '');
    }
    #[When('I edit it to have empty amount of fixed discount in the :channel channel')]
    public function i_edit_it_to_have_empty_fixed_discount_in_channel(Channel_Interface $channel): void
    {
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Amount', '');
    }
    #[When('I filter by :channel channel')]
    public function i_filter_by_channel(Channel_Interface $channel): void
    {
        $this->filter_element->choose_channel($channel);
        $this->filter_element->filter();
    }
    #[When('I filter enabled catalog promotions')]
    public function i_filter_enabled_catalog_promotions(): void
    {
        $this->filter_element->choose_enabled();
        $this->filter_element->filter();
    }
    #[When('/^I filter by (active|failed|inactive|processing) state$/')]
    public function i_filter_by_state(string $state): void
    {
        $this->filter_element->choose_state(ucfirst($state));
        $this->filter_element->filter();
    }
    #[When('/^I filter by (end|start) date up to "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_by_date_up_to(string $date_type, string $date): void
    {
        if ('start' === $date_type) {
            $this->filter_element->specify_start_date_to($date);
        } else {
            $this->filter_element->specify_end_date_to($date);
        }
        $this->filter_element->filter();
    }
    #[When('/^I filter by (end|start) date from "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_by_date_from(string $date_type, string $date): void
    {
        if ('start' === $date_type) {
            $this->filter_element->specify_start_date_from($date);
        } else {
            $this->filter_element->specify_end_date_from($date);
        }
        $this->filter_element->filter();
    }
    #[When('/^I filter by (end|start) date from "(\d{4}-\d{2}-\d{2})" up to "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_by_date_from_date_to_date(string $date_type, string $from_date, string $to_date): void
    {
        if ('start' === $date_type) {
            $this->filter_element->specify_start_date_from($from_date);
            $this->filter_element->specify_start_date_to($to_date);
        } else {
            $this->filter_element->specify_end_date_from($from_date);
            $this->filter_element->specify_end_date_to($to_date);
        }
        $this->filter_element->filter();
    }
    #[When('I request the removal of :catalogPromotion catalog promotion')]
    public function i_request_the_removal_of_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['name' => $catalog_promotion->get_name()]);
    }
    #[When('I sort catalog promotions by :order :field')]
    public function i_sort_catalog_promotion_by_order_field(string $order, string $field): void
    {
        $this->index_page->sort_by(lcfirst(str_replace(' ', '', ucwords($field))), $order === 'descending' ? 'desc' : 'asc');
    }
    #[Then('I should be notified that a discount amount should be between 0% and 100%')]
    public function i_should_be_notified_that_a_discount_amount_should_be_between0and100percent(): void
    {
        Assert::same($this->form_element->get_validation_message('last_action'), 'The percentage discount amount must be between 0% and 100%.');
    }
    #[Then('I should be notified that the percentage amount should be a number and cannot be empty')]
    public function i_should_be_notified_that_the_percentage_amount_should_be_a_number(): void
    {
        Assert::same($this->form_element->get_validation_message('last_action'), 'The percentage discount amount must be a number and can not be empty.');
    }
    #[Then('I should be notified that the fixed amount cannot be empty')]
    public function i_should_be_notified_that_the_fixed_amount_should_cannot_be_empty(): void
    {
        Assert::same($this->form_element->get_validation_message('last_action'), 'Provided configuration contains errors. Please add the fixed discount amount that is a number greater than 0.');
    }
    #[Then('I should be notified that the fixed amount should be a number')]
    public function i_should_be_notified_that_the_fixed_amount_should_be_a_number(): void
    {
        Assert::same($this->form_element->get_validation_message('last_action'), 'Please enter a valid money amount.');
    }
    #[Then('there should be :amount catalog promotions on the list')]
    #[Then('there should be :amount new catalog promotion on the list')]
    #[Then('there should be an empty list of catalog promotions')]
    public function there_should_be_catalog_promotions_on_the_list(int $amount = 0): void
    {
        $this->index_page->open();
        $this->i_should_see_count_catalog_promotions_on_the_list($amount);
    }
    #[Then('I should see :count catalog promotions on the list')]
    public function i_should_see_count_catalog_promotions_on_the_list(int $count): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('the catalog promotions named :firstName and :secondName should be in the registry')]
    #[Then('I should see a catalog promotion with name :name')]
    public function the_catalog_promotions_named_should_be_in_the_registry(string ...$names): void
    {
        foreach ($names as $name) {
            Assert::true($this->index_page->is_single_resource_on_page(['name' => $name]), sprintf('Cannot find catalog promotions with name "%s" in the list', $name));
        }
    }
    #[Then('the catalog promotion named :name should operate between :startDate and :endDate')]
    public function the_catalog_promotion_named_should_operate_between_dates(string $name, string $start_date, string $end_date): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name, 'startDate' => $start_date, 'endDate' => $end_date]), sprintf('Cannot find catalog promotions with name "%s" operating between "%s" and "%s" in the list', $name, $start_date, $end_date));
    }
    #[Then('the catalog promotion named :name should have priority :priority')]
    public function the_catalog_promotion_named_should_have_priority(string $name, int $priority): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name, 'priority' => $priority]), sprintf('Cannot find catalog promotions with name "%s" and priority %s in the list', $name, $priority));
    }
    #[Then('it should have :code code and :name name')]
    public function it_should_have_code_and_name(string $code, string $name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name, 'code' => $code]), sprintf('Cannot find catalog promotions with code "%s" and name "%s" in the list', $code, $name));
    }
    #[Then('it should have priority equal to :priority')]
    public function it_should_have_priority_equal_to(int $priority): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['priority' => $priority]), sprintf('Cannot find catalog promotions with priority "%d"', $priority));
    }
    #[Then('/^("[^"]+" catalog promotion) should apply to ("[^"]+" variant) and ("[^"]+" variant)$/')]
    public function it_should_have_variant_based_scope(Catalog_Promotion_Interface $catalog_promotion, Product_Variant_Interface ...$variants): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $selected_variants = $this->form_element->get_last_scope_names();
        foreach ($variants as $product_variant) {
            Assert::in_array($product_variant->get_name(), $selected_variants);
        }
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[Then('/^("[^"]+" catalog promotion) should apply to all products from ("[^"]+" taxon)$/')]
    public function it_should_have_taxons_based_scope(Catalog_Promotion_Interface $catalog_promotion, Taxon_Interface ...$taxons): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $selected_taxons = $this->form_element->get_last_scope_names();
        foreach ($taxons as $taxon) {
            Assert::in_array($taxon->get_name(), $selected_taxons);
        }
    }
    #[Then('/^this catalog promotion should be applied on ("[^"]+" taxon)$/')]
    public function this_catalog_promotion_should_be_applied_on_taxon(Taxon_Interface $taxon): void
    {
        $selected_taxons = $this->form_element->get_last_scope_names();
        Assert::in_array($taxon->get_name(), $selected_taxons);
    }
    #[Then('/^the ("[^"]+" catalog promotion) should apply to all variants of ("[^"]+" product)$/')]
    public function the_catalog_promotion_should_apply_to_all_variants_of_product(Catalog_Promotion_Interface $catalog_promotion, Product_Interface $product): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        $this->this_catalog_promotion_should_be_applied_on_product($product);
    }
    #[Then('/^this catalog promotion should be applied on ("[^"]+" product)$/')]
    public function this_catalog_promotion_should_be_applied_on_product(Product_Interface $product): void
    {
        $selected_products = $this->form_element->get_last_scope_names();
        Assert::in_array($product->get_name(), $selected_products);
    }
    #[Then('/^it should apply to ("[^"]+" variant) and ("[^"]+" variant)$/')]
    #[Then('/^this catalog promotion should be applied on ("[^"]+" variant)$/')]
    public function it_should_apply_to_variants(Product_Variant_Interface ...$variants): void
    {
        $selected_variants = $this->form_element->get_last_scope_names();
        foreach ($variants as $product_variant) {
            Assert::in_array($product_variant->get_name(), $selected_variants);
        }
    }
    #[Then('/^this catalog promotion should not be applied on ("[^"]+" variant)$/')]
    public function it_should_not_apply_to_variants(Product_Variant_Interface ...$variants): void
    {
        $selected_variants = $this->form_element->get_last_scope_names();
        foreach ($variants as $product_variant) {
            Assert::false(in_array($product_variant->get_name(), $selected_variants, true));
        }
    }
    #[Then('/^it should have "([^"]+)%" discount$/')]
    #[Then('/^this catalog promotion should have "([^"]+)%" percentage discount$/')]
    public function it_should_have_discount(string $amount): void
    {
        Assert::same($this->form_element->get_last_action_option('Amount'), $amount);
    }
    #[Then('/^the ("[^"]+" catalog promotion) should have "(?:€|£|\$)([^"]+)" of fixed discount in the ("[^"]+" channel)$/')]
    #[Then('/^(this catalog promotion) should have "(?:€|£|\$)([^"]+)" of fixed discount in the ("[^"]+" channel)$/')]
    public function the_catalog_promotion_should_have_fixed_discount_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, string $amount, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $catalog_promotion->get_id()]);
        Assert::same($this->form_element->get_last_action_option_for_channel($channel->get_code(), 'Amount'), $amount);
    }
    #[Then('there should still be only one catalog promotion with code :code')]
    public function there_should_still_be_only_one_catalog_promotion_with_code(string $code): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $code]));
    }
    #[Then('the catalog promotion :catalogPromotionName should be available in channel :channelName')]
    public function the_catalog_promotion_should_be_available_in_channel(string $catalog_promotion_name, string $channel_name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $catalog_promotion_name, 'channels' => $channel_name]));
    }
    #[Then('/^(it) should operate between "([^"]+)" and "([^"]+)"$/')]
    #[Then('/^(this catalog promotion) should operate between "([^"]+)" and "([^"]+)"$/')]
    public function the_catalog_promotion_should_operate_between_dates(Catalog_Promotion_Interface $catalog_promotion, string $start_date, string $end_date): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $catalog_promotion->get_name(), 'startDate' => $start_date, 'endDate' => $end_date]));
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[Then('/^(it) should operate between yesterday and tomorrow$/')]
    public function the_catalog_promotion_should_operate_between_yesterday_and_tomorrow(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $catalog_promotion->get_name(), 'startDate' => (new \DateTime('yesterday'))->format('Y-m-d'), 'endDate' => (new \DateTime('tomorrow'))->format('Y-m-d')]));
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
    }
    #[Then('/^(it) should be (inactive|active)$/')]
    #[Then('/^(this catalog promotion) should(?:| still) be (inactive|active)$/')]
    public function it_should_be_inactive(Catalog_Promotion_Interface $catalog_promotion, string $state): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $catalog_promotion->get_name(), 'state' => $state]));
    }
    #[Then('/^(this catalog promotion) should be available in channel "([^"]+)"$/')]
    public function this_catalog_promotion_should_be_available_in_channel(Catalog_Promotion_Interface $catalog_promotion, string $channel_name): void
    {
        $this->index_page->open();
        $this->the_catalog_promotion_should_be_available_in_channel($catalog_promotion->get_name(), $channel_name);
    }
    #[Then('/^(this catalog promotion) should not be available in channel "([^"]+)"$/')]
    public function this_catalog_promotion_should_not_be_available_in_channel(Catalog_Promotion_Interface $catalog_promotion, string $channel_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $catalog_promotion->get_name(), 'channels' => $channel_name]));
    }
    #[Then('I should be notified that catalog promotion has been successfully created')]
    public function i_should_be_notified_that_catalog_promotion_has_been_successfully_created(): void
    {
        $this->notification_checker->check_notification('Catalog promotion has been successfully created.', Notification_Type::success());
    }
    #[Then('I should be notified that code and name are required')]
    public function i_should_be_notified_that_code_and_name_are_required(): void
    {
        Assert::same($this->create_page->get_validation_message('code'), 'Please enter catalog promotion code.');
        Assert::same($this->create_page->get_validation_message('name'), 'Please enter catalog promotion name.');
    }
    #[Then('I should be notified that catalog promotion with this code already exists')]
    public function i_should_be_notified_that_catalog_promotion_with_this_code_already_exists(): void
    {
        Assert::same($this->create_page->get_validation_message('code'), 'The catalog promotion with given code already exists.');
    }
    #[Then('/^(this catalog promotion) name should(?:| still) be "([^"]+)"$/')]
    public function this_catalog_promotion_name_should_be(Catalog_Promotion_Interface $catalog_promotion, string $name): void
    {
        $this->i_browse_catalog_promotions();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $catalog_promotion->get_code(), 'name' => $name]));
    }
    #[Then('/^(this catalog promotion) should be (labelled|described) as "([^"]+)" in ("[^"]+" locale)$/')]
    public function this_catalog_promotion_label_in_locale_should_be(Catalog_Promotion_Interface $catalog_promotion, string $field, string $value, string $locale_code): void
    {
        $fields_mapping = ['labelled' => 'label', 'described' => 'description'];
        Assert::same($this->form_element->get_field_value_in_locale($fields_mapping[$field], $locale_code), $value);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->update_page->is_code_disabled());
    }
    #[Then('I should be notified that at least 1 variant is required')]
    public function i_should_be_notified_that_at_least1variant_is_required(): void
    {
        Assert::same($this->form_element->get_validation_message('last_scope'), 'Please add at least 1 variant.');
    }
    #[Then('/^I should be notified that I must add at least one (product|taxon)$/')]
    public function i_should_be_notified_that_i_must_add_at_least_one(string $entity): void
    {
        Assert::same($this->form_element->get_validation_message('last_scope'), sprintf('Provided configuration contains errors. Please add at least 1 %s.', $entity));
    }
    #[Then('I should not be able to edit it due to wrong state')]
    public function i_should_not_be_able_to_edit_it_due_to_wrong_state(): void
    {
        Assert::in_array('The catalog promotion cannot be edited as it is currently being processed.', $this->form_element->get_validation_messages());
    }
    #[Then('its name should be :name')]
    public function its_name_should_be(string $name): void
    {
        Assert::same($this->show_page->get_name(), $name);
    }
    #[Then('it should reduce price by :amount')]
    public function this_catalog_promotion_should_have_percentage_discount(string $amount): void
    {
        Assert::true($this->show_page->has_action_with_percentage_discount($amount));
    }
    #[Then('it should reduce price by :amount in the :channel channel')]
    public function it_should_reduce_price_by_in_the_channel(string $amount, Channel_Interface $channel): void
    {
        Assert::true($this->show_page->has_action_with_fixed_discount($amount, $channel));
    }
    #[Then('it should apply on :variant variant')]
    public function it_should_apply_on_variant(Product_Variant_Interface $variant): void
    {
        Assert::true($this->show_page->has_scope_with_variant($variant));
    }
    #[Then('it should apply on :product product')]
    public function it_should_apply_on_product(Product_Interface $product): void
    {
        Assert::true($this->show_page->has_scope_with_product($product));
    }
    #[Given('it should be exclusive')]
    public function it_should_be_exclusive(): void
    {
        Assert::true($this->show_page->is_exclusive());
    }
    #[Given('it should not be exclusive')]
    public function it_should_not_be_exclusive(): void
    {
        Assert::false($this->show_page->is_exclusive());
    }
    #[Then('it should start at :startDate and end at :endDate')]
    public function it_should_start_at_and_end_at(string $start_date, string $end_date): void
    {
        Assert::contains($this->show_page->get_start_date(), $start_date);
        Assert::contains($this->show_page->get_end_date(), $end_date);
    }
    #[Then('I should get information that the end date cannot be set before start date')]
    public function i_should_get_information_that_the_end_date_cannot_be_set_before_start_date(): void
    {
        Assert::same($this->form_element->get_validation_message('end_date_date'), 'End date cannot be set before start date.');
    }
    #[Then('I should be notified that not all channels are filled')]
    public function i_should_be_notified_that_not_all_channels_are_filled(): void
    {
        Assert::contains($this->form_element->get_validation_message('last_action'), 'Provided configuration contains errors. Please add the fixed discount amount that is a number greater than 0.');
    }
    #[Then('its priority should be :priority')]
    public function its_priority_should_be(int $priority): void
    {
        Assert::same($this->show_page->get_priority(), $priority);
    }
    #[Then('I should see the catalog promotion scope configuration form')]
    public function i_should_see_the_catalog_promotion_scope_configuration_form(): void
    {
        Assert::true($this->form_element->check_if_scope_configuration_form_is_visible(), 'Catalog promotion scope configuration form is not visible.');
    }
    #[Then('I should see the catalog promotion action configuration form')]
    public function i_should_see_the_catalog_promotion_action_configuration_form(): void
    {
        Assert::true($this->form_element->check_if_action_configuration_form_is_visible(), 'Catalog promotion action configuration form is not visible.');
    }
    #[Then('I should not see a catalog promotion with name :name')]
    public function i_should_not_see_a_catalog_promotion_with_name(string $name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $name]), sprintf('Catalog promotion with name "%s" has been found, but should not.', $name));
    }
    #[Then('the first catalog promotion should have code :code')]
    public function the_first_catalog_promotion_should_have_code(string $code): void
    {
        Assert::same($this->index_page->get_column_fields('code')[0], $code);
    }
    private function create_catalog_promotion(string $name, int $priority, bool $exclusive, Product_Interface $product, string $discount, string $channel): void
    {
        $this->create_page->open();
        $this->create_page->specify_code(String_Inflector::name_to_code($name));
        $this->form_element->label_it($name, 'en_US');
        $this->form_element->name_it($name);
        $this->form_element->prioritize_it($priority);
        $this->form_element->set_exclusiveness($exclusive);
        $this->form_element->check_channel($channel);
        $this->form_element->add_scope(In_For_Product_Scope_Variant_Checker::TYPE);
        $this->form_element->select_scope_option([$product->get_name()]);
        $this->form_element->add_action(Percentage_Discount_Price_Calculator::TYPE);
        $this->form_element->fill_action_option('Amount', $discount);
        $this->create_page->create();
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->create_page;
    }
}