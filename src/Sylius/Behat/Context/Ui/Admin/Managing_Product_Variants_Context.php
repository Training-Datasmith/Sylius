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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Context\Ui\Admin\Helper\Validation_Trait;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Product_Variant\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Variant\Generate_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Variant\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Variant\Update_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final class Managing_Product_Variants_Context implements Context
{
    use Validation_Trait;
    private const HUGE_NUMBER = '2147483647';
    public function __construct(private Shared_Storage_Interface $shared_storage, private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Generate_Page_Interface $generate_page, private Current_Page_Resolver_Interface $current_page_resolver, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('/^I want to create a new variant of (this product)$/')]
    public function i_want_to_create_a_new_product(Product_Interface $product): void
    {
        $this->create_page->open(['productId' => $product->get_id()]);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('I name it :name in :language')]
    public function i_name_it_in(string $name, string $language): void
    {
        $this->create_page->name_it_in($name, $language);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I change its :optionName option to :optionValue')]
    public function i_change_its_option_to(string $option_name, string $option_value): void
    {
        $this->update_page->select_option(strtoupper($option_name), $option_value);
    }
    #[When('I disable its inventory tracking')]
    public function i_disable_its_tracking(): void
    {
        $this->update_page->disable_tracking();
    }
    #[When('I enable its inventory tracking')]
    public function i_enable_its_tracking(): void
    {
        $this->update_page->enable_tracking();
    }
    #[When('/^I set its(?:| default) price to "(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    #[When('I do not set its price')]
    public function i_set_its_price_to(?string $price = null, ?Channel_Interface $channel = null): void
    {
        $this->create_page->specify_price($price ?? '', $channel ?? $this->shared_storage->get('channel'));
    }
    #[When('I set its price to a huge number for the :channel channel')]
    public function i_set_its_price_to_huge_number_for_the_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_price_to(self::HUGE_NUMBER, $channel);
    }
    #[When('I set its original price to a huge number for the :channel channel')]
    public function i_set_its_original_price_to_huge_number_for_the_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_original_price_to(self::HUGE_NUMBER, $channel);
    }
    #[When('I set its minimum price to a huge number for the :channel channel')]
    public function i_set_its_minimum_price_as_out_of_range_value_for_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_minimum_price_to(self::HUGE_NUMBER, $channel);
    }
    #[When('/^I set its price to "-(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_negative_price_to(string $price, Channel_Interface $channel): void
    {
        $this->create_page->specify_price('-' . $price, $channel);
    }
    #[When('/^I set its minimum price to "(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_minimum_price_to(string $price, Channel_Interface $channel): void
    {
        $this->create_page->specify_minimum_price($price, $channel);
    }
    #[When('I remove its price from :channel channel')]
    public function i_remove_its_price_for_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_price_to('', $channel);
    }
    #[When('/^I set its original price to "(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_original_price_to(string $original_price, Channel_Interface $channel): void
    {
        $this->create_page->specify_original_price($original_price, $channel);
    }
    #[When('/^I set its minimum price to "-(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_negative_minimum_price_to(string $price, Channel_Interface $channel): void
    {
        $this->create_page->specify_minimum_price('-' . $price, $channel);
    }
    #[When('/^I set its original price to "-(?:€|£|\$)([^"]+)" for ("([^"]+)" channel)$/')]
    public function i_set_its_negative_original_price_to(string $original_price, Channel_Interface $channel): void
    {
        $this->create_page->specify_original_price('-' . $original_price, $channel);
    }
    #[When('I set its height, width, depth and weight to :number')]
    public function i_set_its_dimensions_to(string $value): void
    {
        $this->create_page->specify_height_width_depth_and_weight($value, $value, $value, $value);
    }
    #[When('I do not specify its current stock')]
    public function i_do_net_set_its_current_stock_to(): void
    {
        $this->create_page->specify_current_stock('');
    }
    #[When('I choose :calculatorName calculator')]
    public function i_choose_calculator(string $calculator_name): void
    {
        $this->create_page->choose_pricing_calculator($calculator_name);
    }
    #[When('I set its :optionName option to :optionValue')]
    public function i_set_its_option_as(string $option_name, string $option_value): void
    {
        $this->create_page->select_option($option_name, $option_value);
    }
    #[When('I set the position of :name to :position')]
    public function i_set_the_position_of_to(string $name, int $position): void
    {
        $this->index_page->set_position($name, $position);
    }
    #[When('I save my new elements order')]
    public function i_save_my_new_elements_order(): void
    {
        $this->index_page->save_positions();
    }
    #[When('I do not want to have shipping required for this product( variant)')]
    public function i_do_not_want_to_have_shipping_required_for_this_product(): void
    {
        $this->create_page->set_shipping_required(false);
    }
    #[When('I check (also) the :productVariantName product variant')]
    public function i_check_the_product_variant_name(string $product_variant_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $product_variant_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('/^I delete the ("[^"]+" variant of product "[^"]+")$/')]
    #[When('/^I try to delete the ("[^"]+" variant of product "[^"]+")$/')]
    public function i_delete_the_variant_of_product(Product_Variant_Interface $product_variant): void
    {
        $this->index_page->open(['productId' => $product_variant->get_product()->get_id()]);
        $this->index_page->delete_resource_on_page(['code' => $product_variant->get_code()]);
    }
    #[When('/^I want to modify the ("[^"]+" product variant)$/')]
    public function i_want_to_modify_a_product(Product_Variant_Interface $product_variant): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
    }
    #[When('/^I choose to show this product in the (channel "([^"]+)")$/')]
    public function i_choose_to_show_this_product_in_the_channel(Channel_Interface $channel): void
    {
        $this->update_page->show_product_in_channel($channel);
    }
    #[When('I choose to show this product in this channel')]
    public function i_choose_to_show_this_product_in_this_channel(): void
    {
        $this->update_page->show_product_in_single_channel();
    }
    #[When('I generate it')]
    #[When('I try to generate it')]
    public function i_click_generate(): void
    {
        $this->generate_page->generate();
    }
    #[When('/^I specify that the (\d)(?:st|nd|rd|th) variant is identified by "([^"]+)" code and costs "(?:€|£|\$)([^"]+)" in (("[^"]+") channel)$/')]
    public function i_specify_there_are_variants_identified_by_code_with_cost(int $nth_variant, string $code, int $price, Channel_Interface $channel): void
    {
        $this->generate_page->specify_code($nth_variant - 1, $code);
        $this->generate_page->specify_price($nth_variant - 1, $price, $channel->get_code());
    }
    #[When('/^I specify that the (\d)(?:st|nd|rd|th) variant is identified by "([^"]+)" code$/')]
    public function i_specify_there_are_variants_identified_by_code(int $nth_variant, string $code): void
    {
        $this->generate_page->specify_code($nth_variant - 1, $code);
    }
    #[When('/^I specify that the (\d)(?:st|nd|rd|th) variant costs "(?:€|£|\$)([^"]+)" in (("[^"]+") channel)$/')]
    public function i_specify_there_are_variants_with_cost(int $nth_variant, int $price, Channel_Interface $channel): void
    {
        $this->generate_page->specify_price($nth_variant - 1, $price, $channel->get_code());
    }
    #[When('/^I remove (\d)(?:st|nd|rd|th) variant from the list$/')]
    public function i_remove_variant_from_the_list(int $nth_variant): void
    {
        $this->generate_page->remove_variant($nth_variant - 1);
    }
    #[When('I set its shipping category as :shippingCategoryName')]
    public function i_set_its_shipping_category_as(string $shipping_category_name): void
    {
        $this->create_page->select_shipping_category($shipping_category_name);
    }
    #[When('I do not specify any information about variants')]
    public function i_do_not_specify_any_information_about_variants(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I change its quantity of inventory to :amount')]
    public function i_change_its_quantity_of_inventory_to(int $amount): void
    {
        $this->update_page->specify_current_stock($amount);
    }
    #[When('/^I want to generate new variants for (this product)$/')]
    #[When('/^I try to generate new variants for (this product)$/')]
    public function i_try_to_generate_new_variants_for_this_product(Product_Interface $product): void
    {
        $this->generate_page->open(['productId' => $product->get_id()]);
    }
    #[When('/^I disable it$/')]
    public function i_disable_it(): void
    {
        $this->update_page->disable();
    }
    #[When('/^I enable it$/')]
    public function i_enable_it(): void
    {
        $this->update_page->enable();
    }
    #[When('/^I change its price to "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/')]
    public function i_change_its_price_to_for_channel(int $price, Channel_Interface $channel): void
    {
        $this->update_page->specify_price($price, $channel);
    }
    #[When('I want to see the list of variants of the :product product')]
    public function i_want_to_see_the_list_of_variants_of_the_product(Product_Interface $product): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
    }
    #[When('I go to generate variants page')]
    public function i_go_to_generate_variants_page(): void
    {
        $this->index_page->go_to_variant_generation();
    }
    #[Then('I should be on the :product product generate variants page')]
    public function i_should_be_on_the_product_generate_variants_page(Product_Interface $product): void
    {
        $this->generate_page->verify(['productId' => $product->get_id()]);
    }
    #[Then('/^the (variant with code "[^"]+") should be priced at (?:€|£|\$)([^"]+) for (channel "([^"]+)")$/')]
    #[Then('/^the (variant with code "[^"]+") should be priced at "(?:€|£|\$)([^"]+)" for (channel "([^"]+)")$/')]
    public function the_variant_with_code_should_be_priced_at_for_channel(Product_Variant_Interface $product_variant, string $price, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
        Assert::same($this->update_page->get_price_for_channel($channel), $price);
    }
    #[Then('/^the (variant with code "[^"]+") should have minimum price (?:€|£|\$)([^"]+) for (channel "([^"]+)")$/')]
    #[Then('/^the (variant with code "[^"]+") should have minimum price "(?:€|£|\$)([^"]+)" for (channel "([^"]+)")$/')]
    public function the_variant_with_code_should_have_minimum_price_for_channel(Product_Variant_Interface $product_variant, string $price, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
        Assert::same($this->update_page->get_minimum_price_for_channel($channel), $price);
    }
    #[Then('/^the (variant with code "[^"]+") should be originally priced at (?:€|£|\$)([^"]+) for (channel "[^"]+")$/')]
    #[Then('/^the (variant with code "[^"]+") should be originally priced at "(?:€|£|\$)([^"]+)" for (channel "[^"]+")$/')]
    public function the_variant_with_code_should_be_original_priced_at_for_channel(Product_Variant_Interface $product_variant, string $price, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
        Assert::same($this->update_page->get_original_price_for_channel($channel), $price);
    }
    #[Then('/^the (variant with code "[^"]+") should be named "([^"]+)" in ("([^"]+)" locale)$/')]
    public function the_variant_with_code_should_be_named_in(Product_Variant_Interface $product_variant, $name, string $language): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
        Assert::same($this->update_page->get_name_in_language($language), $name);
    }
    #[Then('/^the (variant with code "[^"]+") should have an original price of (?:€|£|\$)([^"]+) for (channel "([^"]+)")$/')]
    #[Then('/^the (variant with code "[^"]+") should have an original price of "(?:€|£|\$)([^"]+)" for (channel "([^"]+)")$/')]
    public function the_variant_with_code_should_have_an_original_price_of_for_channel(Product_Variant_Interface $product_variant, $original_price, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
        Assert::same($this->update_page->get_original_price_for_channel($channel), $original_price);
    }
    #[Then('I should be notified that this variant is in use and cannot be deleted')]
    public function i_should_be_notified_of_failure(): void
    {
        $this->notification_checker->check_notification('Cannot delete, the Product variant is in use.', Notification_Type::failure());
    }
    #[Then('the code field should be disabled')]
    public function the_code_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_code_disabled());
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        $this->assert_validation_message($element, sprintf('Please enter the %s.', $element));
    }
    #[Then('I should be notified that code has to be unique')]
    public function i_should_be_notified_that_code_has_to_be_unique(): void
    {
        $this->assert_validation_message('code', 'Product variant code must be unique.');
    }
    #[Then('I should be notified that current stock is required')]
    public function i_should_be_notified_that_on_hand_is_required(): void
    {
        $this->assert_validation_message('on_hand', 'Please enter on hand.');
    }
    #[Then('I should be notified that height, width, depth and weight cannot be lower than 0')]
    public function i_should_be_notified_that_is_height_width_depth_weight_cannot_be_lower_than(): void
    {
        $this->assert_validation_message('height', 'Height cannot be negative.');
        $this->assert_validation_message('width', 'Width cannot be negative.');
        $this->assert_validation_message('depth', 'Depth cannot be negative.');
        $this->assert_validation_message('weight', 'Weight cannot be negative.');
    }
    #[Then('I should be notified that price cannot be lower than 0')]
    public function i_should_be_notified_that_price_cannot_be_lower_then(): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::contains($current_page->get_prices_validation_message(), 'Price cannot be lower than 0.');
    }
    #[Then('I should be notified that price cannot be greater than max value allowed')]
    public function i_should_be_notified_that_price_cannot_be_greater_than_max_value_allowed(): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::contains($current_page->get_prices_validation_message(), sprintf('Value must be less than %s.', self::HUGE_NUMBER));
    }
    #[Then('I should be notified that this variant already exists')]
    public function i_should_be_notified_that_this_variant_already_exists(): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::same($current_page->get_validation_message_for_form(), 'Variant with this option set already exists.');
    }
    #[Then('/^I should be notified that code is required for the (\d)(?:st|nd|rd|th) variant$/')]
    public function i_should_be_notified_that_code_is_required_for_variant($position): void
    {
        Assert::same($this->generate_page->get_validation_message('code', ['%position%' => $position - 1]), 'Please enter the code.');
    }
    #[Then('/^I should be notified that price for the (\d+)(?:|st|nd|rd|th) variant in ("([^"]+)" channel) must be defined$/')]
    public function i_should_be_notified_that_price_for_the_variant_in_channel_must_be_defined(int $position, Channel_Interface $channel): void
    {
        Assert::same($this->generate_page->get_validation_message('price', ['%channel_code%' => $channel->get_code(), '%position%' => $position - 1]), 'You must define price.');
    }
    #[Then('/^I should be notified that variant code must be unique within this product for the (\d)(?:st|nd|rd|th) variant$/')]
    public function i_should_be_notified_that_variant_code_must_be_unique_within_this_product_for_yhe_variant($position): void
    {
        Assert::same($this->generate_page->get_validation_message('code', ['%position%' => $position - 1]), 'This code must be unique within this product.');
    }
    #[Then('I should be notified that prices in :channel channel must be defined')]
    public function i_should_be_notified_that_prices_in_all_channels_must_be_defined(Channel_Interface $channel): void
    {
        Assert::contains($this->create_page->get_validation_message('price', ['%channel_code%' => $channel->get_code()]), 'You must define price');
    }
    #[Then('/^inventory of (this variant) should not be tracked$/')]
    public function this_product_variant_should_not_be_tracked(Product_Variant_Interface $product_variant): void
    {
        $this->i_want_to_modify_a_product($product_variant);
        Assert::false($this->update_page->is_tracked());
    }
    #[Then('/^inventory of (this variant) should be tracked$/')]
    public function this_product_variant_should_be_tracked(Product_Variant_Interface $product_variant): void
    {
        $this->i_want_to_modify_a_product($product_variant);
        Assert::true($this->update_page->is_tracked());
    }
    #[Then('I should be notified that it has been successfully generated')]
    public function i_should_be_notified_that_it_has_been_successfully_generated(): void
    {
        $this->notification_checker->check_notification('Success Product variants have been successfully generated.', Notification_Type::success());
    }
    #[Then('I should not be able to generate any variants')]
    public function i_should_not_be_able_to_generate_any_variants(): void
    {
        Assert::false($this->generate_page->is_generation_possible());
    }
    #[Then('/^the (variant with code "[^"]+") should not have shipping required$/')]
    public function the_variant_with_code_should_not_have_shipping_required(Product_Variant_Interface $product_variant): void
    {
        $this->update_page->open(['productId' => $product_variant->get_product()->get_id(), 'id' => $product_variant->get_id()]);
        Assert::false($this->update_page->is_shipping_required());
    }
    #[Then('I should be notified that on hand quantity must be greater than the number of on hold units')]
    public function i_should_be_notified_that_on_hand_quantity_must_be_greater_than_the_number_of_on_hold_units(): void
    {
        Assert::same($this->update_page->get_validation_message('on_hand'), 'On hand must be greater than the number of on hold units');
    }
    #[Then('I should be notified that variants cannot be generated from options without any values')]
    public function i_should_be_notified_that_variants_cannot_be_generated_from_options_without_any_values(): void
    {
        $this->notification_checker->check_notification('Cannot generate variants for a product without options values', Notification_Type::failure());
    }
    #[Then('I should not have configured price for :channel channel')]
    public function i_should_not_have_configured_price_for_channel(Channel_Interface $channel): void
    {
        $product_variant = $this->shared_storage->get('variant');
        $this->update_page->open(['productId' => $product_variant->get_product()->get_id(), 'id' => $product_variant->get_id()]);
        Assert::same($this->update_page->get_price_for_channel($channel), '');
    }
    #[Then('I should have original price equal to :price in :channel channel')]
    public function i_should_have_original_price_equal_in_channel(string $price, Channel_Interface $channel): void
    {
        $product_variant = $this->shared_storage->get('variant');
        $this->update_page->open(['productId' => $product_variant->get_product()->get_id(), 'id' => $product_variant->get_id()]);
        Assert::contains($price, $this->update_page->get_original_price_for_channel($channel));
    }
    #[Then('I should see the :optionName option as :valueName')]
    public function i_should_see_the_option_as(string $option_name, string $value_name): void
    {
        Assert::true($this->update_page->is_selected_option_value_on_page($option_name, $value_name));
    }
    #[Then('I should not be able to show this product in shop')]
    public function i_should_not_be_able_to_show_this_product_in_shop(): void
    {
        Assert::true($this->update_page->is_show_in_shop_button_disabled());
    }
    #[Then('/^(this variant) should be disabled$/')]
    public function this_variant_should_be_disabled(Product_Variant_Interface $product_variant): void
    {
        $this->i_want_to_modify_a_product($product_variant);
        Assert::false($this->update_page->is_enabled());
    }
    #[Then('/^(this variant) should be enabled$/')]
    public function this_variant_should_be_enabled(Product_Variant_Interface $product_variant): void
    {
        $this->i_want_to_modify_a_product($product_variant);
        Assert::true($this->update_page->is_enabled());
    }
    #[Then('I should (also) see a variant named :name')]
    public function i_should_see_a_variant_named(string $name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name]));
    }
    #[Then('I should (also) see :count variant(s) with no name')]
    public function i_should_see_count_variants_with_no_name(int $count = 1): void
    {
        Assert::same($this->index_page->count_items_with_no_name(), $count);
    }
    #[Then('the variant :productVariant should have :optionName option as :optionValue')]
    public function the_variant_should_have_option_as(Product_Variant_Interface $product_variant, string $option_name, string $option_value): void
    {
        $this->update_page->open(['id' => $product_variant->get_id(), 'productId' => $product_variant->get_product()->get_id()]);
        Assert::true($this->update_page->is_selected_option_value_on_page($option_name, $option_value));
    }
    #[Then('/^I should not be able to remove (\d)(?:st|nd|rd|th) product variant$/')]
    public function i_should_not_be_able_to_remove(int $nth_variant): void
    {
        Assert::false($this->generate_page->is_product_variant_removable($nth_variant - 1));
    }
    #[Then('/^I should be able to remove (\d)(?:st|nd|rd|th) product variant$/')]
    public function i_should_be_able_to_remove(int $nth_variant): void
    {
        Assert::true($this->generate_page->is_product_variant_removable($nth_variant - 1));
    }
    #[Then('I should not be able to go to the generate variants page')]
    public function i_should_not_be_able_to_go_to_the_generate_variants_page(): void
    {
        Assert::false($this->index_page->has_generate_variants_button(), 'Generate variants button should not be visible');
    }
    /**
     * @param string $element
     */
    private function assert_validation_message($element, string $message): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::same($current_page->get_validation_message($element), $message);
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
    }
}