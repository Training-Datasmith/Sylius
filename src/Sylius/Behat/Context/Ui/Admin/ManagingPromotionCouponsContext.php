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
use Sylius\Behat\Element\Admin\Promotion_Coupon\Form_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Promotion_Coupon\Generate_Page_Interface;
use Sylius\Behat\Page\Admin\Promotion_Coupon\Index_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Core\Model\Promotion_Coupon_Interface;
use Sylius\Component\Promotion\Model\Promotion_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Promotion_Coupons_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Generate_Page_Interface $generate_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('/^I am browsing coupons of (this promotion)$/')]
    #[Given('/^I browse coupons of (this promotion)$/')]
    #[When('/^I want to view all coupons of (this promotion)$/')]
    #[When('/^I browse all coupons of ("[^"]+" promotion)$/')]
    public function i_want_to_view_all_coupons_of_this_promotion(Promotion_Interface $promotion): void
    {
        $this->index_page->open(['promotionId' => $promotion->get_id()]);
    }
    #[When('/^I want to create a new coupon for (this promotion)$/')]
    public function i_want_to_create_a_new_coupon_for_this_promotion(Promotion_Interface $promotion): void
    {
        $this->create_page->open(['promotionId' => $promotion->get_id()]);
    }
    #[When('/^I want to modify the ("[^"]+" coupon) for (this promotion)$/')]
    public function i_want_to_modify_the_coupon(Promotion_Coupon_Interface $coupon, Promotion_Interface $promotion): void
    {
        $this->update_page->open(['id' => $coupon->get_id(), 'promotionId' => $promotion->get_id()]);
    }
    #[When('/^I want to generate new coupons for (this promotion)$/')]
    public function i_want_to_generate_new_coupons_for_this_promotion(Promotion_Interface $promotion): void
    {
        $this->generate_page->open(['promotionId' => $promotion->get_id()]);
    }
    #[When('/^I specify their code length as (\d+)$/')]
    #[When('I do not specify their code length')]
    public function i_specify_their_code_length_as(?int $code_length = null): void
    {
        $this->generate_page->specify_code_length($code_length);
    }
    #[When('I specify their prefix as :prefix')]
    public function specify_prefix_as(string $prefix): void
    {
        $this->generate_page->specify_prefix($prefix);
    }
    #[When('I specify their suffix as :suffix')]
    public function specify_suffix_as(string $suffix): void
    {
        $this->generate_page->specify_suffix($suffix);
    }
    #[When('/^I limit generated coupons usage to (\d+) times?$/')]
    public function i_set_generated_coupons_usage_limit_to(int $limit): void
    {
        $this->generate_page->set_usage_limit($limit);
    }
    #[When('I make generated coupons valid until :date')]
    public function i_make_generated_coupons_valid_until(\DateTimeInterface $date): void
    {
        $this->generate_page->set_expires_at($date);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->form_element->specify_code($code ?? '');
    }
    #[When('I specify a too long code')]
    public function i_specify_a_too_long(): void
    {
        $this->form_element->specify_code(str_repeat('a', 256));
    }
    #[When('I limit its usage to :limit time(s)')]
    public function i_limit_its_usage_limit_to(int $limit): void
    {
        $this->form_element->set_usage_limit($limit);
    }
    #[When('I change its usage limit to :limit')]
    public function i_change_its_usage_limit_to(int $limit): void
    {
        $this->form_element->set_usage_limit($limit);
    }
    #[When('I specify its amount as :amount')]
    #[When('I do not specify its amount')]
    #[When('I choose the amount of :amount coupons to be generated')]
    public function i_specify_its_amount_as(?int $amount = null): void
    {
        $this->generate_page->specify_amount($amount);
    }
    #[When('/^I limit its per customer usage to ([^"]+) times?$/')]
    public function i_limit_its_per_customer_usage_limit_to(int $limit): void
    {
        $this->form_element->set_customer_usage_limit($limit);
    }
    #[When('I change its per customer usage limit to :limit')]
    public function i_change_its_per_customer_usage_limit_to(int $limit): void
    {
        $this->form_element->set_customer_usage_limit($limit);
    }
    #[When('I make it not reusable from cancelled orders')]
    public function i_make_it_reusable_from_cancelled_orders(): void
    {
        $this->form_element->toggle_reusable_from_cancelled_orders(false);
    }
    #[When('I make it valid until :date')]
    public function i_make_it_valid_until(\DateTimeInterface $date): void
    {
        $this->form_element->set_expires_at($date);
    }
    #[When('I change its expiration date to :date')]
    public function i_change_its_expiration_date_to(\DateTimeInterface $date): void
    {
        $this->form_element->set_expires_at($date);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I generate it')]
    #[When('I generate these coupons')]
    #[When('I try to generate it')]
    #[When('I try to generate these coupons')]
    public function i_generate_it(): void
    {
        $this->generate_page->generate();
    }
    #[When('/^I delete ("[^"]+" coupon) related to (this promotion)$/')]
    #[When('/^I try to delete ("[^"]+" coupon) related to (this promotion)$/')]
    public function i_delete_coupon_related_to_this_promotion(Promotion_Coupon_Interface $coupon, Promotion_Interface $promotion): void
    {
        $this->index_page->open(['promotionId' => $promotion->get_id()]);
        $this->index_page->delete_resource_on_page(['code' => $coupon->get_code()]);
    }
    #[When('I check (also) the :couponCode coupon')]
    public function i_check_the_coupon(string $coupon_code): void
    {
        $this->index_page->check_resource_on_page(['code' => $coupon_code]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('/^I sort coupons by (ascending|descending) number of uses$/')]
    public function i_sort_coupons_by_number_of_uses(string $order): void
    {
        $this->sort_by($order, 'used');
    }
    #[When('/^I sort coupons by (ascending|descending) code$/')]
    public function i_sort_coupons_by_code(string $order): void
    {
        $this->sort_by($order, 'code');
    }
    #[When('/^I sort coupons by (ascending|descending) usage limit$/')]
    public function i_sort_coupons_by_usage_limit(string $order): void
    {
        $this->sort_by($order, 'usageLimit');
    }
    #[When('/^I sort coupons by (ascending|descending) usage limit per customer$/')]
    public function i_sort_coupons_by_per_customer_usage_limit(string $order): void
    {
        $this->sort_by($order, 'perCustomerUsageLimit');
    }
    #[When('/^I sort coupons by (ascending|descending) expiration date$/')]
    public function i_sort_coupons_by_expiration_date(string $order): void
    {
        $this->sort_by($order, 'expiresAt');
    }
    #[Then('/^there should(?:| still) be (\d+) coupons? related to (this promotion)$/')]
    public function there_should_be_coupon_related_to(int $number, Promotion_Interface $promotion): void
    {
        $this->index_page->open(['promotionId' => $promotion->get_id()]);
        Assert::same($this->index_page->count_items(), $number);
    }
    #[When('I filter by code containing :phrase')]
    public function i_filter_by_code_containing(string $phrase): void
    {
        $this->index_page->filter_by_code($phrase);
        $this->index_page->filter();
    }
    #[Then('all of the coupon codes should be prefixed with :prefix')]
    public function all_of_the_coupon_codes_should_be_prefixed_with(string $prefix): void
    {
        foreach ($this->index_page->get_column_fields('code') as $coupon_code) {
            Assert::starts_with($coupon_code, $prefix);
        }
    }
    #[Then('all of the coupon codes should be suffixed with :suffix')]
    public function all_of_the_coupon_codes_should_be_suffixed_with(string $suffix): void
    {
        foreach ($this->index_page->get_column_fields('code') as $coupon_code) {
            Assert::ends_with($coupon_code, $suffix);
        }
    }
    #[Then('I should see a single coupon in the list')]
    public function i_should_see_a_single_coupon_in_the_list(): void
    {
        Assert::same($this->index_page->count_items(), 1);
    }
    #[Then('there should be a coupon with code :code')]
    #[Then('there should be a :promotion promotion with a coupon code :code')]
    #[Then('I should see the promotion coupon :code in the list')]
    public function there_should_be_coupon_with_code(string $code): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $code]));
    }
    #[Then('this coupon should be valid until :date')]
    public function this_coupon_should_be_valid_until(\DateTime $date): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['expiresAt' => $date->format('d-m-Y')]));
    }
    #[Then('/^this coupon should have (\d+) usage limit$/')]
    public function this_coupon_should_have_usage_limit($limit): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['usageLimit' => $limit]));
    }
    #[Then('/^("[^"]+" coupon) should be used (\d+) time(?:|s)$/')]
    public function coupon_should_have_usage_limit(Promotion_Coupon_Interface $promotion_coupon, int $used): void
    {
        Assert::same($this->index_page->get_used_number($promotion_coupon->get_code()), $used);
    }
    #[Then('/^this coupon should have (\d+) per customer usage limit$/')]
    public function this_coupon_should_have_per_customer_usage_limit($limit): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['perCustomerUsageLimit' => $limit]));
    }
    #[Then('/^(this coupon) should not be reusable from cancelled orders$/')]
    public function this_coupon_should_be_reusable_from_cancelled_orders(Promotion_Coupon_Interface $coupon): void
    {
        $this->update_page->open(['id' => $coupon->get_id(), 'promotionId' => $coupon->get_promotion()->get_id()]);
        Assert::false($this->form_element->is_reusable_from_cancelled_orders());
    }
    #[Then('I should not be able to edit its code')]
    #[Then('the code field should be disabled')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[Then('I should be notified that code is too long')]
    public function i_should_be_notified_that_code_is_too_long(): void
    {
        Assert::contains($this->form_element->get_validation_message('code'), 'must not be longer than 255 characters.');
    }
    #[Then('I should be notified that coupon with this code already exists')]
    public function i_should_be_notified_that_coupon_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'This coupon already exists.');
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::same($this->form_element->get_validation_message($element), sprintf('Please enter coupon %s.', $element));
    }
    #[Then('I should be notified that generate amount is required')]
    public function i_should_be_notified_that_generate_amount_is_required(): void
    {
        Assert::same($this->generate_page->get_validation_message('amount'), 'Please enter amount of coupons to generate.');
    }
    #[Then('I should be notified that generate code length is required')]
    public function i_should_be_notified_that_code_length_is_required(): void
    {
        Assert::same($this->generate_page->get_validation_message('code_length'), 'Please enter coupon code length.');
    }
    #[Then('I should be notified that generate code length is out of range')]
    public function i_should_be_notified_that_code_length_is_out_of_range(): void
    {
        Assert::same($this->generate_page->get_validation_message('code_length'), 'Coupon code length must be between 1 and 40.');
    }
    #[Then('/^there should still be only one coupon with code "([^"]+)" related to (this promotion)$/')]
    public function there_should_still_be_only_one_coupon_with_code_related_to($code, Promotion_Interface $promotion): void
    {
        $this->index_page->open(['promotionId' => $promotion->get_id()]);
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $code]));
    }
    #[Then('I should be notified that coupon usage limit must be at least one')]
    public function i_should_be_notified_that_coupon_usage_limit_must_be_at_least(): void
    {
        Assert::same($this->form_element->get_validation_message('usage_limit'), 'Coupon usage limit must be at least 1.');
    }
    #[Then('I should be notified that coupon usage limit per customer must be at least one')]
    public function i_should_be_notified_that_coupon_usage_limit_per_customer_must_be_at_least(): void
    {
        Assert::same($this->form_element->get_validation_message('per_customer_usage_limit'), 'Coupon usage limit per customer must be at least 1.');
    }
    #[Then('/^(this coupon) should no longer exist in the coupon registry$/')]
    public function coupon_should_not_exist_in_the_registry(Promotion_Coupon_Interface $coupon): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $coupon->get_code()]));
    }
    #[Then('I should be notified that they have been successfully generated')]
    public function i_should_be_notified_that_they_have_been_successfully_generated(): void
    {
        $this->notification_checker->check_notification('Success Promotion coupons have been successfully generated.', Notification_Type::success());
    }
    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function i_should_be_notified_of_failure(): void
    {
        $this->notification_checker->check_notification('Error Cannot delete, the Promotion coupon is in use.', Notification_Type::error());
    }
    #[Then('/^(this coupon) should still exist in the registry$/')]
    public function coupon_should_still_exist_in_the_registry(Promotion_Coupon_Interface $coupon): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $coupon->get_code()]));
    }
    #[Then('I should be notified that generating :amount coupons with code length equal to :codeLength is not possible')]
    public function i_should_be_notified_that_generating_coupons_with_code_length_is_not_possible(int $amount, int $code_length): void
    {
        Assert::contains($this->generate_page->get_form_validation_message(), sprintf('Invalid coupons code length or coupons amount. It is not possible to generate %d unique coupons with code length %d.', $amount, $code_length));
    }
    #[Then('I should see the coupon :couponCode in the list')]
    public function i_should_see_the_coupon_in_the_list(string $coupon_code): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $coupon_code]));
    }
    #[Then('I should see :count coupons on the list')]
    public function i_should_see_count_coupons_on_the_list(int $count): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('the first coupon should have code :code')]
    public function the_first_coupon_should_have_code(string $code): void
    {
        Assert::same($this->index_page->get_column_fields('code')[0], $code);
    }
    #[Then('I should see a single promotion coupon in the list')]
    #[Then('there should be :amount promotion coupons')]
    public function there_should_be_promotion_coupon(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    private function sort_by(string $order, string $field): void
    {
        $this->index_page->sort_by($field, str_starts_with($order, 'de') ? 'desc' : 'asc');
    }
}