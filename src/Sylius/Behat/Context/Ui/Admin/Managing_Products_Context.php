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
use Sylius\Behat\Element\Admin\Product\Associations_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Attributes_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Channel_Pricings_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Media_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Taxonomy_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Translations_Form_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Create_Configurable_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Create_Simple_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Index_Per_Taxon_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Update_Configurable_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Update_Simple_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Review\Index_Page_Interface as ProductReviewIndexPageInterface;
use Sylius\Behat\Page\Admin\Product_Variant\Create_Page_Interface as VariantCreatePageInterface;
use Sylius\Behat\Page\Admin\Product_Variant\Generate_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Variant\Update_Page_Interface as VariantUpdatePageInterface;
use Sylius\Behat\Service\Helper\Java_Script_Test_Helper_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Products_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Shared_Storage_Interface $shared_storage, private Create_Simple_Product_Page_Interface $create_simple_product_page, private Create_Configurable_Product_Page_Interface $create_configurable_product_page, private Index_Page_Interface $index_page, private Update_Simple_Product_Page_Interface $update_simple_product_page, private Update_Configurable_Product_Page_Interface $update_configurable_product_page, private Product_Review_Index_Page_Interface $product_review_index_page, private Index_Per_Taxon_Page_Interface $index_per_taxon_page, private Variant_Create_Page_Interface $variant_create_page, private Generate_Page_Interface $variant_generate_page, private Current_Page_Resolver_Interface $current_page_resolver, private Notification_Checker_Interface $notification_checker, private Variant_Update_Page_Interface $variant_update_page, private Java_Script_Test_Helper_Interface $test_helper, private Associations_Form_Element_Interface $associations_form_element, private Attributes_Form_Element_Interface $attributes_form_element, private Channel_Pricings_Form_Element_Interface $channel_pricings_form_element, private Media_Form_Element_Interface $media_form_element, private Taxonomy_Form_Element_Interface $taxonomy_form_element, private Translations_Form_Element_Interface $translations_form_element)
    {
    }
    #[When('I want to create a new simple product')]
    public function i_want_to_create_a_new_simple_product(): void
    {
        $this->test_helper->wait_until_page_opens($this->create_simple_product_page);
    }
    #[When('I want to create a new configurable product')]
    public function i_want_to_create_a_new_configurable_product(): void
    {
        $this->test_helper->wait_until_page_opens($this->create_configurable_product_page);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $current_page = $this->resolve_current_page();
        $current_page->specify_code($code ?? '');
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I name it :name in :localeCode locale')]
    #[When('I rename it to :name in :localeCode locale')]
    #[When('I should be able to name it :name in :localeCode locale')]
    public function i_rename_it_to_in_locale(string $name, string $locale_code): void
    {
        $this->translations_form_element->name_it_in($name, $locale_code);
    }
    #[When('I remove its name from :localeCode translation')]
    public function i_remove_its_name_from_translation(string $locale_code): void
    {
        $this->translations_form_element->name_it_in('', $locale_code);
    }
    #[When('I generate its slug in :localeCode locale')]
    public function i_generate_its_slug_in(string $locale_code): void
    {
        $this->translations_form_element->generate_slug($locale_code);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        /** @var CreatePageInterface $currentPage */
        $current_page = $this->resolve_current_page();
        $current_page->create();
    }
    #[When('I disable its inventory tracking')]
    public function i_disable_its_tracking(): void
    {
        $this->update_simple_product_page->disable_tracking();
    }
    #[When('I enable its inventory tracking')]
    public function i_enable_its_tracking(): void
    {
        $this->update_simple_product_page->enable_tracking();
    }
    #[When('/^I set its(?:| default) price to "(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_price_to(string $price, Channel_Interface $channel): void
    {
        $this->channel_pricings_form_element->specify_price($channel, $price);
    }
    #[When('/^I set its original price to "(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_original_price_to(int $original_price, Channel_Interface $channel): void
    {
        $this->channel_pricings_form_element->specify_original_price($channel, $original_price);
    }
    #[When('I make it available in channel :channel')]
    public function i_make_it_available_in_channel(Channel_Interface $channel): void
    {
        $this->create_simple_product_page->check_channel($channel->get_code());
    }
    #[When('I enable it in channel :channel')]
    public function i_enable_it_in_channel(Channel_Interface $channel): void
    {
        // Temporary solution until we will make current page resolver work with product pages
        $this->update_configurable_product_page->check_channel($channel->get_code());
    }
    #[When('I set its slug to :slug')]
    #[When('I set its slug to :slug in :localeCode locale')]
    #[When('I remove its slug')]
    public function i_set_its_slug_to_in(?string $slug = null, string $locale_code = 'en_US'): void
    {
        $this->translations_form_element->specify_slug_in($slug, $locale_code);
    }
    #[When('I choose to show this product in the :channel channel')]
    public function i_choose_to_show_this_product_in_the_channel(Channel_Interface $channel): void
    {
        $this->update_simple_product_page->show_product_in_channel($channel);
    }
    #[When('I choose to show this product in this channel')]
    public function i_choose_to_show_this_product_in_this_channel(): void
    {
        $this->update_simple_product_page->show_product_in_single_channel();
    }
    #[When('I choose :channelName as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(string $channel_name): void
    {
        $this->index_page->choose_channel_filter($channel_name);
    }
    #[When('I choose enabled filter')]
    public function i_choose_enabled_filter(): void
    {
        $this->index_page->choose_enabled_filter();
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[Then('I should see the product :productName in the list')]
    #[Then('the product :productName should appear in the store')]
    #[Then('the product :productName should be in the shop')]
    #[Then('this product should still be named :productName')]
    public function the_product_should_appear_in_the_shop(string $product_name): void
    {
        $this->i_want_to_browse_products();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_name]));
    }
    #[Given('I am browsing products')]
    #[When('I browse products')]
    #[When('I want to browse products')]
    public function i_want_to_browse_products(): void
    {
        $this->index_page->open();
    }
    #[When('/^I am browsing products from ("([^"]+)" taxon)$/')]
    public function i_am_browsing_products_from_taxon(Taxon_Interface $taxon): void
    {
        $this->index_per_taxon_page->open(['taxonId' => $taxon->get_id()]);
    }
    #[When('/^I am browsing the (\d+)(?:st|nd|rd|th) page of products from ("([^"]+)" taxon)$/')]
    #[When('/^I go to the (\d+)(?:st|nd|rd|th) page of products from ("([^"]+)" taxon)$/')]
    public function i_am_browsing_products_from_taxon_page(int $page, Taxon_Interface $taxon): void
    {
        $this->index_per_taxon_page->open(['taxonId' => $taxon->get_id(), 'page' => $page]);
    }
    #[When('I filter them by :taxonName taxon')]
    public function i_filter_them_by_taxon(string $taxon_name): void
    {
        $this->index_page->filter_by_taxon($taxon_name);
        $this->index_page->filter();
    }
    #[When('I filter them by :productName product')]
    public function i_filter_them_by_product(string $product_name): void
    {
        $this->index_per_taxon_page->filter_by_name($product_name);
        $this->index_per_taxon_page->filter();
    }
    #[When('I filter them by :taxonName main taxon')]
    public function i_filter_them_by_main_taxon(string $taxon_name): void
    {
        $this->index_page->filter_by_main_taxon($taxon_name);
        $this->index_page->filter();
    }
    #[When('I check (also) the :productName product')]
    public function i_check_the_product(string $product_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $product_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should( still) see a product with :field :value')]
    public function i_should_see_product_with(string $field, string $value): void
    {
        Assert::true($this->index_page->is_single_resource_on_page([$field => $value]));
    }
    #[Then('I should not see any product with :field :value')]
    public function i_should_not_see_any_product_with(string $field, string $value): void
    {
        Assert::false($this->index_page->is_single_resource_on_page([$field => $value]));
    }
    #[Then('the first product on the list should have :field :value')]
    #[Then('the first product on the list within this taxon should have :field :value')]
    public function the_first_product_on_the_list_should_have(string $field, string $value): void
    {
        $current_page = $this->resolve_current_page();
        Assert::same($current_page->get_column_fields($field)[0], $value);
    }
    #[Then('/^the (\d+)(?:st|nd|rd|th) product on this page should be named "([^"]+)"$/')]
    public function the_nth_product_on_this_page_should_be_named(int $position, string $value): void
    {
        $values = $this->index_per_taxon_page->get_column_fields('name');
        Assert::same($values[$position - 1], $value);
        $this->shared_storage->set('product_taxon_name', $value);
    }
    #[Then('this product should be at position :position')]
    public function the_nth_product_on_this_page_should_be_at_position(int $position): void
    {
        $product_name = $this->shared_storage->get('product_taxon_name');
        Assert::same($this->index_per_taxon_page->get_product_position($product_name), $position);
    }
    #[Then('the one before last product on the list should have :field :value')]
    public function the_one_before_last_product_on_the_list_should_have(string $field, string $value): void
    {
        $values = $this->index_per_taxon_page->get_column_fields($field);
        Assert::same($values[count($values) - 2], $value);
        $this->shared_storage->set('product_taxon_name', $value);
    }
    #[Then('the one before last product on the list should have name :productName with position :position')]
    public function the_one_before_last_product_on_the_list_should_have_name_with_position(string $product_name, int $position): void
    {
        $product_names = $this->index_per_taxon_page->get_column_fields('name');
        Assert::same($product_names[count($product_names) - 2], $product_name);
        Assert::same($this->index_per_taxon_page->get_product_position($product_name), $position);
        $this->shared_storage->set('product_taxon_name', $product_name);
    }
    #[Then('the one before last image on the list should have type :type with position :position')]
    public function the_one_before_last_image_on_the_list_should_have_name_with_position(string $image_type, int $position): void
    {
        $images = $this->media_form_element->get_images();
        if (count($images) < 2) {
            throw new \Exception('There are less than two images on the list.');
        }
        $one_before_last_image = $images[count($images) - 2];
        $this->media_form_element->assert_image_type_and_position($one_before_last_image, $image_type, $position);
    }
    #[Then('the last image on the list should have type :type with position :position')]
    public function the_last_image_on_the_list_should_have_name_with_position(string $image_type, int $position): void
    {
        $images = $this->media_form_element->get_images();
        $last_image = end($images);
        $this->media_form_element->assert_image_type_and_position($last_image, $image_type, $position);
    }
    #[Then('the last product on the list should have :field :value')]
    #[Then('the last product on the list within this taxon should have :field :value')]
    public function the_last_product_on_the_list_should_have(string $field, string $value): void
    {
        $values = $this->index_per_taxon_page->get_column_fields($field);
        Assert::same(end($values), $value);
        $this->shared_storage->set('product_taxon_name', $value);
    }
    #[Then('the last product on the list should have name :productName with position :position')]
    public function the_last_product_on_the_list_should_have_name_with_position(string $product_name, int $position): void
    {
        $product_names = $this->index_per_taxon_page->get_column_fields('name');
        Assert::same(end($product_names), $product_name);
        Assert::same($this->index_per_taxon_page->get_product_position($product_name), $position);
        $this->shared_storage->set('product_taxon_name', $product_name);
    }
    #[When('I switch the way products are sorted :sortType by :field')]
    #[When('I start sorting products by :field')]
    #[When('the products are already sorted :sortType by :field')]
    #[When('I sort the products :sortType by :field')]
    public function i_sort_products_by(string $field): void
    {
        $this->index_page->sort_by($field);
    }
    #[When('I sort this taxon\'s products :sortType by :field')]
    public function i_sort_this_taxons_products_by(string $sort_type, string $field): void
    {
        $this->index_per_taxon_page->sort_by($field, str_starts_with($sort_type, 'de') ? 'desc' : 'asc');
    }
    #[Then('I should see a single product in the list')]
    #[Then('I should see :numberOfProducts products in the list')]
    public function i_should_see_products_in_the_list(int $number_of_products = 1): void
    {
        Assert::same($this->index_page->count_items(), $number_of_products);
    }
    #[Then('/^(this product) should not exist in the product catalog$/')]
    public function product_should_not_exist(Product_Interface $product): void
    {
        $this->i_want_to_browse_products();
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $product->get_code()]));
    }
    #[Then('I should be notified that this product is in use and cannot be deleted')]
    public function i_should_be_notified_of_failure(): void
    {
        $this->notification_checker->check_notification('Cannot delete, the Product is in use.', Notification_Type::error());
    }
    #[Then('/^(this product) should still exist in the product catalog$/')]
    public function product_should_exist_in_the_product_catalog(Product_Interface $product): void
    {
        $this->the_product_should_appear_in_the_shop($product->get_name());
    }
    #[When('I want to modify the :product product')]
    #[When('/^I want to modify (this product)$/')]
    #[When('/^I want to edit (this product)$/')]
    #[When('I modify the :product product')]
    #[When('I want to modify the images of :product product')]
    public function i_want_to_modify_a_product(Product_Interface $product): void
    {
        $this->shared_storage->set('product', $product);
        $this->test_helper->wait_until_page_opens($this->update_simple_product_page, ['id' => $product->get_id()]);
    }
    #[When('/^I go to the (\d)(?:st|nd|rd|th) page$/')]
    public function i_go_to_page(int $page): void
    {
        $this->index_page->go_to_page($page);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $current_page = $this->resolve_current_page();
        Assert::true($current_page->is_code_disabled());
    }
    #[Then('this product name should be :name in :localeCode locale')]
    public function this_product_name_should_be(string $name, string $locale_code): void
    {
        Assert::true($this->translations_form_element->has_name_in_locale($name, $locale_code), sprintf('Product should have "%s" name in "%s" locale.', $name, $locale_code));
    }
    #[Then('/^I should be notified that (code|name|slug) is required$/')]
    public function i_should_be_notified_that_is_required(string $element, string $locale_code = 'en_US'): void
    {
        $validation_message = match ($element) {
            'name' => $this->translations_form_element->get_validation_message('name', ['%locale_code%' => $locale_code]),
            'slug' => $this->translations_form_element->get_validation_message('slug', ['%locale_code%' => $locale_code]),
            'code' => $this->resolve_current_page()->get_validation_message('code'),
            default => throw new \InvalidArgumentException(sprintf('There is no validation message for "%s" element.', $element)),
        };
        Assert::same($validation_message, sprintf('Please enter product %s.', $element));
    }
    #[Then('I should be notified that meta keywords are too long')]
    public function i_should_be_notified_that_meta_keywords_are_too_long(): void
    {
        Assert::same($this->translations_form_element->get_validation_message('meta_keywords', ['%locale_code%' => 'en_US']), 'Product meta keywords must not be longer than 255 characters.');
    }
    #[Then('I should be notified that meta description is too long')]
    public function i_should_be_notified_that_meta_description_is_too_long(): void
    {
        Assert::same($this->translations_form_element->get_validation_message('meta_description', ['%locale_code%' => 'en_US']), 'Product meta description must not be longer than 255 characters.');
    }
    #[When('I cancel my changes')]
    public function i_cancel_changes(): void
    {
        $current_page = $this->resolve_current_page();
        $current_page->cancel_changes();
    }
    #[When('/^I change its price to (?:€|£|\$)([^"]+) for ("([^"]+)" channel)$/')]
    public function i_change_its_price_to(string $price, Channel_Interface $channel): void
    {
        $this->channel_pricings_form_element->specify_price($channel, $price);
    }
    #[When('/^I change its original price to "(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_change_its_original_price_to(int $original_price, Channel_Interface $channel): void
    {
        $this->channel_pricings_form_element->specify_original_price($channel, $original_price);
    }
    #[Given('I add the :optionName option to it')]
    public function i_add_the_option_to_it(string $option_name): void
    {
        $this->create_configurable_product_page->select_option($option_name);
    }
    #[When('I add the :attributeName attribute')]
    #[When('I add the :attributeName attribute to it')]
    public function i_add_the_attribute(string $attribute_name): void
    {
        $this->attributes_form_element->add_attribute($attribute_name);
    }
    #[When('I set its :attributeName attribute to :value in :localeCode locale')]
    #[When('I do not set its :attributeName attribute in :localeCode locale')]
    #[When('I set the :attributeName attribute value to :value in :localeCode locale')]
    public function i_set_its_attribute_to_in_locale(string $attribute_name, ?string $value = null, string $locale_code = 'en_US'): void
    {
        $this->attributes_form_element->update_attribute($attribute_name, $value ?? '', $locale_code);
    }
    #[When('I select :value value in :localeCode for the :attribute attribute')]
    public function i_select_value_in_language_for_the_attribute(string $value, string $locale_code, string $attribute): void
    {
        $this->attributes_form_element->update_attribute($attribute, $value, $locale_code);
    }
    #[When('I select :value value for the :attribute attribute')]
    public function i_select_value_for_the_attribute(string $value, string $attribute): void
    {
        $this->attributes_form_element->update_attribute($attribute, $value, '');
    }
    #[When('I set its non-translatable :attributeName attribute to :value')]
    public function i_set_its_non_translatable_attribute_to(string $attribute_name, string $value): void
    {
        $this->attributes_form_element->update_attribute($attribute_name, $value, '');
    }
    #[When('I remove its :attribute attribute')]
    #[When('I remove its :attribute attribute from :localeCode')]
    public function i_remove_its_attribute(string $attribute, string $locale_code = 'en_US'): void
    {
        $this->attributes_form_element->remove_attribute($attribute, $locale_code);
    }
    #[When('I try to add new attributes')]
    public function i_try_to_add_new_attributes(): void
    {
        $this->attributes_form_element->add_selected_attributes();
    }
    #[When('I do not want to have shipping required for this product')]
    public function i_do_not_want_to_have_shipping_required_for_this_product(): void
    {
        $this->create_simple_product_page->set_shipping_required(false);
    }
    #[Then('attribute :attributeName of product :product should be :value')]
    #[Then('attribute :attributeName of product :product should be :value in :localeCode locale')]
    public function its_attribute_should_be(string $attribute_name, Product_Interface $product, string $value, string $locale_code = 'en_US'): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::same($this->attributes_form_element->get_attribute_value($attribute_name, $locale_code), $value);
    }
    #[Then('select attribute :attributeName of product :product should be :value in :localeCode locale')]
    #[Then('select attribute :attributeName of product :product should be :value')]
    public function its_select_attribute_should_be_in_locale(string $attribute_name, Product_Interface $product, string $value, string $locale_code = ''): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::same($this->attributes_form_element->get_attribute_value($attribute_name, $locale_code), $value);
    }
    #[Then('non-translatable attribute :attributeName of product :product should be :value')]
    public function its_non_translatable_attribute_should_be(string $attribute_name, Product_Interface $product, string $value): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::same($this->attributes_form_element->get_attribute_value($attribute_name, ''), $value);
    }
    #[Then('/^(product "[^"]+") should not have a "([^"]+)" attribute$/')]
    public function product_should_not_have_attribute(Product_Interface $product, string $attribute): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::false($this->attributes_form_element->has_attribute($attribute));
    }
    #[Then('/^product "[^"]+" should not have any attributes$/')]
    #[Then('/^product "[^"]+" should have (\d+) attributes?$/')]
    public function product_should_not_have_any_attributes(int $count = 0): void
    {
        Assert::same($this->attributes_form_element->get_number_of_attributes(), $count);
    }
    #[Then('product with :element :value should not be added')]
    public function product_with_name_should_not_be_added(string $element, string $value): void
    {
        $this->i_want_to_browse_products();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[When('I set its meta keywords to too long string in :localeCode')]
    public function i_set_its_meta_keywords_to_too_long_string_in(string $locale_code): void
    {
        $this->translations_form_element->set_meta_keywords(str_repeat('a', 256), $locale_code);
    }
    #[When('I set its meta description to too long string in :localeCode')]
    public function i_set_its_meta_description_to_too_long_string_in(string $locale_code): void
    {
        $this->translations_form_element->set_meta_description(str_repeat('a', 256), $locale_code);
    }
    #[When('I want to choose main taxon for product :product')]
    public function i_want_to_choose_main_taxon_for_product(Product_Interface $product): void
    {
        $this->i_want_to_modify_a_product($product);
        $current_page = $this->resolve_current_page();
        $current_page->open(['id' => $product->get_id()]);
    }
    #[Then('I should be able to choose taxon :taxonName from the list')]
    public function i_should_be_able_to_choose_taxon_for_this_product(string $taxon_name): void
    {
        Assert::true($this->taxonomy_form_element->is_taxon_visible_in_main_taxon_list($taxon_name));
    }
    #[Then('I should not be able to choose taxon :taxonName from the list')]
    public function i_should_not_be_able_to_choose_taxon_for_this_product(string $taxon_name): void
    {
        Assert::false($this->taxonomy_form_element->is_taxon_visible_in_main_taxon_list($taxon_name));
    }
    #[Then('/^this product should have (?:a|an) "([^"]+)" option$/')]
    public function this_product_should_have_option(string $product_option): void
    {
        $this->update_configurable_product_page->is_product_option_chosen($product_option);
    }
    #[Then('I should not be able to edit its options')]
    public function i_should_not_be_able_to_edit_its_options(): void
    {
        Assert::true($this->update_configurable_product_page->is_product_options_disabled());
    }
    #[When('/^I choose main (taxon "[^"]+")$/')]
    #[Then('/^I should be able to choose main (taxon "[^"]+")$/')]
    public function i_choose_main_taxon(Taxon_Interface $taxon): void
    {
        $this->taxonomy_form_element->select_main_taxon($taxon->get_name());
    }
    #[Then('I should see non-translatable attribute :attribute with value :value%')]
    public function i_should_see_non_translatable_attribute_with_value(string $attribute, string $value): void
    {
        Assert::same($this->attributes_form_element->get_value_non_translatable_attribute($attribute), $value);
    }
    #[Then('/^the slug of the ("[^"]+" product) should(?:| still) be "([^"]+)"$/')]
    #[Then('/^the slug of the ("[^"]+" product) should(?:| still) be "([^"]+)" (in the "[^"]+" locale)$/')]
    public function product_slug_should_be(Product_Interface $product, string $slug, string $locale_code = 'en_US'): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::same($this->translations_form_element->get_slug($locale_code), $slug);
    }
    #[Then('/^(this product) main taxon should be "([^"]+)"$/')]
    #[Then('/^main taxon of (product "[^"]+") should be "([^"]+)"$/')]
    public function this_product_main_taxon_should_be(Product_Interface $product, string $taxon_name): void
    {
        Assert::same($taxon_name, $this->taxonomy_form_element->get_main_taxon());
    }
    #[Then('/^inventory of (this product) should not be tracked$/')]
    public function this_product_should_not_be_tracked(Product_Interface $product): void
    {
        $this->i_want_to_modify_a_product($product);
        Assert::false($this->update_simple_product_page->is_tracked());
    }
    #[Then('/^inventory of (this product) should be tracked$/')]
    public function this_product_should_be_tracked(Product_Interface $product): void
    {
        $this->i_want_to_modify_a_product($product);
        Assert::true($this->update_simple_product_page->is_tracked());
    }
    #[When('I attach the :path image with :type type')]
    #[When('I attach the :path image')]
    #[When('I attach the :path image with :type type to this product')]
    #[When('I attach the :path image to this product')]
    public function i_attach_image_with_type(string $path, ?string $type = null): void
    {
        $this->media_form_element->attach_image($path, $type);
    }
    #[When('I attach the :path image with selected :productVariant variant to this product')]
    public function i_attach_image_with_selected_variant_to_this_product(string $path, Product_Variant_Interface $product_variant): void
    {
        $this->media_form_element->attach_image(path: $path, productVariant: $product_variant);
    }
    #[When('I select :productVariant variant for the first image')]
    public function i_select_variant_for_the_first_image(Product_Variant_Interface $product_variant): void
    {
        $this->media_form_element->select_variant_for_first_image($product_variant);
    }
    #[When('I associate as :productAssociationType the :productName product')]
    #[When('I associate as :productAssociationType the :firstProductName and :secondProductName products')]
    #[Then('I should be able to associate as :productAssociationType the :productName product')]
    public function i_associate_products_as_product_association(Product_Association_Type_Interface $product_association_type, string ...$products_names): void
    {
        $this->associations_form_element->associate_products($product_association_type, $products_names);
    }
    #[When('I remove an associated product :product from :productAssociationType')]
    public function i_remove_an_associated_product_from_product_association(Product_Interface $product, Product_Association_Type_Interface $product_association_type): void
    {
        $this->associations_form_element->remove_associated_product($product, $product_association_type);
    }
    #[When('I go to the variants list')]
    public function i_go_to_the_variants_list(): void
    {
        $this->resolve_current_page()->go_to_variants_list();
    }
    #[When('I go to the variant creation page')]
    public function i_go_to_the_variant_creation_page(): void
    {
        $this->resolve_current_page()->go_to_variant_creation();
    }
    #[When('I go to the variant generation page')]
    public function i_go_to_the_variant_generation_page(): void
    {
        $this->resolve_current_page()->go_to_variant_generation();
    }
    #[Then('/^(?:this product|the product "[^"]+"|it) should(?:| also) have an image with "([^"]*)" type$/')]
    public function this_product_should_have_an_image_with_type(string $type): void
    {
        Assert::true($this->media_form_element->has_image_with_type($type));
    }
    #[Then('its image should have :productVariant variant selected')]
    public function its_image_should_have_variant_selected(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->media_form_element->has_image_with_variant($product_variant), sprintf('Expected variant "%s" to be selected, but got "%s".', $product_variant->get_name(), $this->media_form_element->get_first_image_selected_variant_name() ?? 'none'));
    }
    #[Then('/^the (product "[^"]+") should still have an accessible image$/')]
    public function product_should_still_have_an_accessible_image(Product_Interface $product): void
    {
        Assert::true($this->index_page->has_product_accessible_image($product->get_code()));
    }
    #[Then('/^(?:this product|it)(?:| also) should not have any images with "([^"]*)" type$/')]
    public function this_product_should_not_have_any_images_with_type(string $code): void
    {
        Assert::false($this->media_form_element->has_image_with_type($code));
    }
    #[When('I change the image with the :type type to :path')]
    public function i_change_its_image_to_path_for_the_type(string $type, string $path): void
    {
        $this->media_form_element->change_image_with_type($type, $path);
    }
    #[When('/^I(?:| also) remove an image with "([^"]*)" type$/')]
    public function i_remove_an_image_with_type(string $code): void
    {
        $this->media_form_element->remove_image_with_type($code);
    }
    #[When('I remove the first image')]
    public function i_remove_the_first_image(): void
    {
        $this->media_form_element->remove_first_image();
    }
    #[When('I change the first image type to :type')]
    public function i_change_the_first_image_type_to(string $type): void
    {
        $this->media_form_element->modify_first_image_type($type);
    }
    #[When('I change the :type image position to :position')]
    public function i_change_the_image_position_to(string $image, int $position): void
    {
        $this->media_form_element->modify_position_of_image_with_type($image, $position);
    }
    #[Then('/^(this product) should not have any images$/')]
    public function this_product_should_not_have_images(Product_Interface $product): void
    {
        $this->i_want_to_modify_a_product($product);
        Assert::same($this->media_form_element->count_images(), 0);
    }
    #[Then('/^(this product) should(?:| still) have (?:only one|(\d+)) images?$/')]
    public function there_should_still_be_only_one_image_in_this_product(Product_Interface $product, int $count = 1): void
    {
        $this->i_want_to_modify_a_product($product);
        Assert::same($this->media_form_element->count_images(), $count);
    }
    #[Then('/^there should be no reviews of (this product)$/')]
    public function there_are_no_product_reviews(Product_Interface $product): void
    {
        $this->product_review_index_page->open();
        Assert::false($this->product_review_index_page->is_single_resource_on_page(['reviewSubject' => $product->get_name()]));
    }
    #[Then('this product should( also) have an association :productAssociationType with product :product')]
    public function the_product_should_have_an_association_with_product(Product_Association_Type_Interface $product_association_type, Product_Interface $product): void
    {
        Assert::true($this->associations_form_element->has_associated_product($product, $product_association_type), sprintf('This product should have an association %s with product %s.', $product_association_type->get_name(), $product->get_name()));
    }
    /**
     *
     * @param array<ProductInterface> $products
     */
    #[Then('/^this product should have an (association "[^"]+") with (products "[^"]+" and "[^"]+")$/')]
    #[Then('/^this product should also have an (association "[^"]+") with (products "[^"]+" and "[^"]+")$/')]
    public function the_products_should_have_an_association_with_products(Product_Association_Type_Interface $product_association_type, array $products): void
    {
        foreach ($products as $product) {
            $this->the_product_should_have_an_association_with_product($product_association_type, $product);
        }
    }
    #[Then('this product should not have an association :productAssociationType with product :product')]
    public function the_product_should_not_have_an_association_with_product(Product_Association_Type_Interface $product_association_type, Product_Interface $product): void
    {
        Assert::false($this->associations_form_element->has_associated_product($product, $product_association_type));
    }
    #[Then('I should be notified that original price can not be defined without price')]
    public function i_should_be_notified_that_original_price_can_not_be_defined_without_price(): void
    {
        Assert::same($this->channel_pricings_form_element->get_channel_pricing_validation_message(), 'Original price can not be defined without price');
    }
    #[Then('I should be notified that svg file is not allowed')]
    public function i_should_be_notified_that_svg_type_is_not_allowed(): void
    {
        $this->media_form_element->has_validation_error_with_message('This file type is not allowed.');
    }
    #[Then('I should be notified that simple product code has to be unique')]
    public function i_should_be_notified_that_simple_product_code_has_to_be_unique(): void
    {
        $this->assert_validation_message('code', 'Simple product code must be unique among all products and product variants.');
    }
    #[Then('I should be notified that slug has to be unique')]
    public function i_should_be_notified_that_slug_has_to_be_unique(): void
    {
        Assert::same($this->translations_form_element->get_validation_message('slug', ['%locale_code%' => 'en_US']), 'Product slug must be unique.');
    }
    #[Then('I should be notified that code has to be unique')]
    public function i_should_be_notified_that_code_has_to_be_unique(): void
    {
        $this->assert_validation_message('code', 'Product code must be unique.');
    }
    #[Then('I should be notified that price must be defined for :channel channel')]
    public function i_should_be_notified_that_price_must_be_defined_for_channel(Channel_Interface $channel): void
    {
        Assert::same($this->channel_pricings_form_element->get_validation_message('price', ['%channel_code%' => $channel->get_code()]), 'You must define price.');
    }
    #[Then('they should have order like :firstProductName, :secondProductName and :thirdProductName')]
    public function they_should_have_order_like_and(string ...$product_names): void
    {
        Assert::true($this->index_per_taxon_page->has_products_in_order($product_names));
    }
    #[When('I save my new configuration')]
    public function i_save_my_new_configuration(): void
    {
        $this->index_per_taxon_page->save_positions();
    }
    #[When('I set the position of :productName to :position')]
    public function i_set_the_position_of_to(string $product_name, string $position): void
    {
        $this->index_per_taxon_page->set_position_of_product($product_name, $position);
    }
    #[When('/^I remove its price from ("[^"]+" channel)$/')]
    public function i_remove_its_price_for_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_price_to('', $channel);
    }
    #[Then('this product should( still) have slug :value in :localeCode (locale)')]
    public function this_product_element_should_have_slug_in(string $slug, string $locale_code): void
    {
        $this->test_helper->wait_until_assertion_passes(function () use ($locale_code, $slug): void {
            Assert::same($this->translations_form_element->get_slug($locale_code), $slug);
        });
    }
    #[When('I set its shipping category as :shippingCategoryName')]
    public function i_set_its_shipping_category_as(string $shipping_category_name): void
    {
        $this->create_simple_product_page->select_shipping_category($shipping_category_name);
    }
    #[Then('/^(it|this product) should be priced at (?:€|£|\$)([^"]+) for (channel "([^"]+)")$/')]
    #[Then('/^(product "[^"]+") should be priced at (?:€|£|\$)([^"]+) for (channel "([^"]+)")$/')]
    public function it_should_be_priced_at_for_channel(Product_Interface $product, string $price, Channel_Interface $channel): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::same($this->channel_pricings_form_element->get_price_for_channel($channel), $price);
    }
    #[Then('/^(its|this products) original price should be "(?:€|£|\$)([^"]+)" for (channel "([^"]+)")$/')]
    public function its_original_price_for_channel(Product_Interface $product, string $original_price, Channel_Interface $channel): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::same($this->channel_pricings_form_element->get_original_price_for_channel($channel), $original_price);
    }
    #[Then('/^(this product) should no longer have price for channel "([^"]+)"$/')]
    public function this_product_should_no_longer_have_price_for_channel(Product_Interface $product, string $channel_name): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::true($this->channel_pricings_form_element->has_no_price_for_channel($channel_name), sprintf('Product "%s" should not have price defined for channel "%s".', $product->get_name(), $channel_name));
    }
    #[Then('I should be notified that I have to define product variants\' prices for newly assigned channels first')]
    public function i_should_be_notified_that_i_have_to_define_product_variants_prices_for_newly_assigned_channels_first(): void
    {
        Assert::same($this->update_configurable_product_page->get_validation_message('channels'), 'You have to define product variants\' prices for newly assigned channels first.');
    }
    #[Then('/^the (product "[^"]+") should not have shipping required$/')]
    public function the_product_with_code_should_not_have_shipping_required(Product_Interface $product): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::false($this->update_simple_product_page->is_shipping_required());
    }
    #[Then('I should be notified that I have to define the :attribute attribute in :localeCode locale')]
    public function i_should_be_notified_that_i_have_to_define_the_attribute_in_locale(string $attribute, string $locale_code): void
    {
        Assert::same($this->attributes_form_element->get_attribute_validation_errors($attribute, $locale_code), 'This value should not be blank.');
    }
    #[Then('I should be notified that the :attribute attribute in :localeCode locale should be longer than :number')]
    public function i_should_be_notified_that_the_attribute_in_should_be_longer_than(string $attribute, string $locale_code, int $number): void
    {
        Assert::same($this->attributes_form_element->get_attribute_validation_errors($attribute, $locale_code), sprintf('This value is too short. It should have %s characters or more.', $number));
    }
    #[Then('/^I should be on the variant creation page for (this product)$/')]
    public function i_should_be_on_the_variant_creation_page_for_this_product(Product_Interface $product): void
    {
        Assert::true($this->variant_create_page->is_open(['productId' => $product->get_id()]));
    }
    #[Then('/^I should be on the variant generation page for (this product)$/')]
    public function i_should_be_on_the_variant_generation_page_for_this_product(Product_Interface $product): void
    {
        Assert::true($this->variant_generate_page->is_open(['productId' => $product->get_id()]));
    }
    #[Then('I should see inventory of this product')]
    public function i_should_see_inventory_of_this_product(): void
    {
        Assert::true($this->update_simple_product_page->has_tab('inventory'));
    }
    #[Then('I should not see inventory of this product')]
    public function i_should_not_see_inventory_of_this_product(): void
    {
        Assert::false($this->update_configurable_product_page->has_tab('inventory'));
    }
    #[Then('I should be notified that the position :invalidPosition is invalid')]
    public function i_should_be_notified_that_the_position_is_invalid(string $invalid_position): void
    {
        $this->notification_checker->check_notification(sprintf('The position "%s" is invalid.', $invalid_position), Notification_Type::error());
    }
    #[Then('I should not be able to show this product in shop')]
    public function i_should_not_be_able_to_show_this_product_in_shop(): void
    {
        Assert::true($this->update_simple_product_page->is_show_in_shop_button_disabled());
    }
    #[When('/^I disable it$/')]
    public function i_disable_it(): void
    {
        $this->update_simple_product_page->disable();
    }
    #[Then('/^(this product) should be disabled along with its variant$/')]
    public function this_product_should_be_disabled_along_with_its_variant(Product_Interface $product): void
    {
        Assert::true($product->is_simple());
        $this->i_want_to_modify_a_product($product);
        Assert::false($this->update_simple_product_page->is_enabled());
        $this->variant_update_page->open(['productId' => $product->get_id(), 'id' => $product->get_variants()->first()->get_id()]);
        Assert::false($this->variant_update_page->is_enabled());
    }
    #[When('/^I enable it$/')]
    public function i_enable_it(): void
    {
        $this->update_simple_product_page->enable();
    }
    #[Then('/^(this product) should be enabled along with its variant$/')]
    public function this_product_should_be_enabled_along_with_its_variant(Product_Interface $product): void
    {
        Assert::true($product->is_simple());
        $this->i_want_to_modify_a_product($product);
        Assert::true($this->update_simple_product_page->is_enabled());
        $this->variant_update_page->open(['productId' => $product->get_id(), 'id' => $product->get_variants()->first()->get_id()]);
        Assert::true($this->variant_update_page->is_enabled());
    }
    #[Then('I should not have configured price for :channel channel')]
    public function i_should_not_have_configured_price_for_channel(Channel_Interface $channel): void
    {
        Assert::same($this->channel_pricings_form_element->get_price_for_channel($channel), '');
    }
    #[Then('I should have original price equal to :price in :channel channel')]
    public function i_should_have_original_price_equal_in_channel(string $price, Channel_Interface $channel): void
    {
        Assert::contains($price, $this->channel_pricings_form_element->get_original_price_for_channel($channel));
    }
    #[Then('the first product on the list shouldn\'t have a name')]
    public function the_first_product_on_the_list_should_not_have_name(): void
    {
        Assert::true($this->index_page->check_first_product_has_data_attribute('data-test-missing-translation-paragraph'));
    }
    #[Then('the last product on the list shouldn\'t have a name')]
    public function the_last_product_on_the_list_should_not_have_name(): void
    {
        Assert::true($this->index_page->check_last_product_has_data_attribute('data-test-missing-translation-paragraph'));
    }
    #[Then('I should be redirected to the previous page of only enabled products')]
    public function i_should_be_redirected_to_the_previous_filtered_page_with_filter(): void
    {
        Assert::true($this->index_page->is_enabled_filter_applied());
    }
    #[Then('/^I should be redirected to the ([^"]+)(nd) page of only enabled products$/')]
    public function i_should_be_redirected_to_the_previous_filtered_page_with_filter_and_page(int $page): void
    {
        Assert::true($this->index_page->is_enabled_filter_applied());
        Assert::eq($this->index_page->get_page_number(), $page);
    }
    #[Then('the show product\'s page button should be enabled')]
    public function the_show_products_page_button_should_be_enabled(): void
    {
        Assert::false($this->update_simple_product_page->is_show_in_shop_button_disabled());
    }
    #[Then('the show product\'s page button should be disabled')]
    public function the_show_products_page_button_should_be_disabled(): void
    {
        Assert::true($this->update_simple_product_page->is_show_in_shop_button_disabled());
    }
    #[Then('/^it should be leading to (the product)\'s page in the ("[^"]+" locale)$/')]
    public function it_should_be_leading_to_the_product_page_in_the_locale(Product_Interface $product, string $locale_code): void
    {
        $product_translation = $product->get_translation($locale_code);
        $show_product_page_url = $this->update_simple_product_page->get_show_product_in_single_channel_url();
        Assert::contains($show_product_page_url, sprintf('/%s/products/%s', $locale_code, $product_translation->get_slug()));
    }
    #[Then('I should be notified that the :attributeName attribute value for :localeCode is required')]
    public function i_should_be_notified_that_the_attribute_value_is_required(string $attribute_name, string $locale_code): void
    {
        Assert::true($this->attributes_form_element->has_attribute_error($attribute_name, $locale_code));
    }
    #[Then('I should not be able to go to the generate variants page')]
    public function i_should_not_be_able_to_go_to_the_generate_variants_page(): void
    {
        Assert::false($this->update_simple_product_page->has_generate_variants_button(), 'Generate variants button should not be visible');
    }
    #[Then('I should see the :product product')]
    public function i_should_see_the_product(Product_Interface $product): void
    {
        Assert::true($this->index_per_taxon_page->is_single_resource_on_page(['name' => $product->get_name()]), sprintf('Product with code %s does not exist, but it should', $product->get_code()));
    }
    #[Then('I should not see the :product product')]
    public function i_should_not_see_the_product(Product_Interface $product): void
    {
        Assert::false($this->index_per_taxon_page->is_single_resource_on_page(['name' => $product->get_name()]), sprintf('Product with code %s does not exist, but it should', $product->get_code()));
    }
    private function assert_validation_message(string $element, string $message): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->resolve_current_page();
        Assert::same($current_page->get_validation_message($element), $message);
    }
    private function resolve_current_page(): Create_Configurable_Product_Page_Interface|Create_Simple_Product_Page_Interface|Index_Page_Interface|Index_Per_Taxon_Page_Interface|Update_Configurable_Product_Page_Interface|Update_Simple_Product_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->index_page, $this->index_per_taxon_page, $this->create_simple_product_page, $this->create_configurable_product_page, $this->update_simple_product_page, $this->update_configurable_product_page]);
    }
}