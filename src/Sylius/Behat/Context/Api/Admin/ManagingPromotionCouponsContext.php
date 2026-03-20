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
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Api\Subresources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Component\Core\Model\Promotion_Coupon_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Promotion_Coupons_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Request_Factory_Interface $request_factory, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[Given('/^I am browsing coupons of (this promotion)$/')]
    #[When('/^I want to view all coupons of (this promotion)$/')]
    #[When('/^I browse all coupons of ("[^"]+" promotion)$/')]
    public function i_want_to_view_all_coupons_of_this_promotion(Promotion_Interface $promotion): void
    {
        $this->client->request_get(sprintf('promotions/%s/coupons', $promotion->get_code()));
    }
    #[When('I filter by code containing :phrase')]
    public function i_filter_by_code_containing(string $phrase): void
    {
        $this->client->add_filter('code', $phrase);
        $this->client->filter();
    }
    #[When('I want to create a new coupon for a non-existing promotion')]
    public function i_want_to_create_a_new_coupon_for_non_existing_promotion(): void
    {
        $this->client->build_create_request('promotions/non-existing-promotion/coupons');
    }
    #[When('/^I want to create a new coupon for (this promotion)$/')]
    public function i_want_to_create_a_new_coupon_for_promotion(Promotion_Interface $promotion): void
    {
        $this->client->build_create_request(sprintf('promotions/%s/coupons', $promotion->get_code()));
    }
    #[When('/^I want to modify the ("[^"]+" coupon) for (this promotion)$/')]
    public function i_want_to_modify_the_coupon_for_this_promotion(Promotion_Coupon_Interface $coupon, Promotion_Interface $promotion): void
    {
        $this->client->build_update_request(sprintf('promotions/%s/coupons/%s', $promotion->get_code(), $coupon->get_code()));
    }
    #[When('/^I want to generate new coupons for (this promotion)$/')]
    public function i_want_to_generate_new_coupons_for_this_promotion(Promotion_Interface $promotion): void
    {
        $this->client->build_create_request(sprintf('promotions/%s/coupons/generate', $promotion->get_code()));
    }
    #[When('I (try to) delete :coupon coupon related to this promotion')]
    public function i_delete_coupon_related_to_this_promotion(Promotion_Coupon_Interface $coupon): void
    {
        $this->client->request_delete(sprintf('promotions/%s/coupons/%s', $coupon->get_promotion()->get_code(), $coupon->get_code()));
    }
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(string $code): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I limit its usage to :times time(s)')]
    #[When('I change its usage limit to :times')]
    public function i_limit_its_usage_to_times(int $times): void
    {
        $this->client->add_request_data('usageLimit', $times);
    }
    #[When('I limit its per customer usage to :times time(s)')]
    #[When('I change its per customer usage limit to :times')]
    public function i_limit_its_per_customer_usage_to_times(int $times): void
    {
        $this->client->add_request_data('perCustomerUsageLimit', $times);
    }
    #[When('I make it valid until :date')]
    #[When('I change its expiration date to :date')]
    public function i_make_it_valid_until(\DateTime $date): void
    {
        $this->client->add_request_data('expiresAt', $date->format('d-m-Y'));
    }
    #[When('I make it not reusable from cancelled orders')]
    public function i_make_it_not_reusable_from_cancelled_orders(): void
    {
        $this->client->add_request_data('reusableFromCancelledOrders', false);
    }
    #[When('I choose the amount of :amount coupons to be generated')]
    public function i_specify_its_amount_as(int $amount): void
    {
        $this->client->update_request_data(['amount' => $amount]);
    }
    #[When('I specify their prefix as :prefix')]
    public function i_specify_prefix_as(string $prefix): void
    {
        $this->client->update_request_data(['prefix' => $prefix]);
    }
    #[When('I specify their suffix as :suffix')]
    public function i_specify_suffix_as(string $suffix): void
    {
        $this->client->update_request_data(['suffix' => $suffix]);
    }
    #[When('/^I specify their code length as (\d+)$/')]
    #[When('I do not specify their code length')]
    public function i_specify_their_code_length_as(?int $code_length = null): void
    {
        $this->client->update_request_data(['codeLength' => $code_length]);
    }
    #[When('/^I limit generated coupons usage to (\d+) times?$/')]
    public function i_set_generated_coupons_usage_limit_to(int $limit): void
    {
        $this->client->update_request_data(['usageLimit' => $limit]);
    }
    #[When('I make generated coupons valid until :date')]
    public function i_make_generated_coupons_valid_until(\DateTimeInterface $date): void
    {
        $this->client->update_request_data(['expiresAt' => $date->format('Y-m-d')]);
    }
    #[When('I do not specify its :field')]
    public function i_do_not_specify_its(): void
    {
        // Intentionally left blank
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I (try to) generate these coupons')]
    public function i_generate_these_coupons(): void
    {
        $this->client->request();
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
    public function there_should_be_count_coupons_related_to_this_promotion(int $count, Promotion_Interface $promotion): void
    {
        $coupons = $this->response_checker->get_collection($this->client->sub_resource_index(Resources::PROMOTIONS, Subresources::PROMOTION_COUPONS, $promotion->get_code()));
        Assert::same(count($coupons), $count);
    }
    #[Then('there should be a :promotion promotion with a coupon code :code')]
    public function there_should_be_a_coupon_with_code(Promotion_Interface $promotion, string $code): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->request_get(sprintf('promotions/%s/coupons', $promotion->get_code())), 'code', $code));
    }
    #[Then('there should be no coupon with code :code')]
    public function there_should_be_no_coupon_with_code(string $code): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->request_get('promotions/non-existing-promotion/coupons'), 'code', $code));
    }
    #[Then('I should see :count coupons on the list')]
    public function i_should_see_count_coupons_on_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('the first coupon should have code :code')]
    public function the_first_coupon_should_have_code(string $code): void
    {
        Assert::true($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), 0, 'code', $code));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Promotion coupon could not be created');
    }
    #[Then('this coupon should be valid until :date')]
    public function this_coupon_should_be_valid_until(\DateTime $date): void
    {
        $actual_date = \DateTime::create_from_format('Y-m-d h:i:s', $this->response_checker->get_value($this->client->get_last_response(), 'expiresAt'));
        Assert::same($actual_date->format('Y-m-d'), $date->format('Y-m-d'));
    }
    #[Then('this coupon should have :limit usage limit')]
    public function this_coupon_should_have_usage_limit(int $limit): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'usageLimit'), $limit);
    }
    #[Then('this coupon should have :limit per customer usage limit')]
    public function this_coupon_should_have_per_customer_usage_limit(int $limit): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'perCustomerUsageLimit'), $limit);
    }
    #[Then('this coupon should not be reusable from cancelled orders')]
    public function this_coupon_should_not_be_reusable_from_cancelled_orders(): void
    {
        Assert::false($this->response_checker->get_value($this->client->get_last_response(), 'reusableFromCancelledOrders'));
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Promotion coupon could not be deleted');
    }
    #[Then('/^(this coupon) should no longer exist in the coupon registry$/')]
    public function coupon_should_not_exist_in_the_registry(Promotion_Coupon_Interface $coupon): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->request_get(sprintf('promotions/%s/coupons', $coupon->get_promotion()->get_code())), 'code', $coupon->get_code()));
    }
    #[Then('/^(this coupon) should still exist in the registry$/')]
    public function coupon_should_still_exist_in_the_registry(Promotion_Coupon_Interface $coupon): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->request_get(sprintf('promotions/%s/coupons', $coupon->get_promotion()->get_code())), 'code', $coupon->get_code()));
    }
    #[Then('all of the coupon codes should be prefixed with :prefix')]
    public function all_of_the_coupon_codes_should_be_prefixed_with(string $prefix): void
    {
        foreach ($this->response_checker->get_collection($this->client->get_last_response()) as $promotion_coupon) {
            Assert::starts_with($promotion_coupon['code'], $prefix);
        }
    }
    #[Then('all of the coupon codes should be suffixed with :suffix')]
    public function all_of_the_coupon_codes_should_be_suffixed_with(string $suffix): void
    {
        foreach ($this->response_checker->get_collection($this->client->get_last_response()) as $promotion_coupon) {
            Assert::ends_with($promotion_coupon['code'], $suffix);
        }
    }
    #[Then('/^there should still be only one coupon with code "([^"]+)" related to (this promotion)$/')]
    public function there_should_still_be_only_one_coupon_with_code_related_to(string $code, Promotion_Interface $promotion): void
    {
        $coupons = $this->response_checker->get_collection_items_with_value($this->client->sub_resource_index(Resources::PROMOTIONS, Subresources::PROMOTION_COUPONS, $promotion->get_code()), 'code', $code);
        Assert::count($coupons, 1);
    }
    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function i_should_be_notified_that_it_is_in_use_and_cannot_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the promotion coupon is in use.');
    }
    #[Then('I should be notified that code is required')]
    public function i_should_be_notified_that_code_is_required(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Coupon has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: Please enter coupon code.');
    }
    #[Then('I should be notified that coupon usage limit must be at least one')]
    public function i_should_be_notified_that_coupon_usage_limit_must_be_at_least_one(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Coupon has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'usageLimit: Coupon usage limit must be at least 1.');
    }
    #[Then('I should be notified that coupon usage limit per customer must be at least one')]
    public function i_should_be_notified_that_coupon_usage_limit_per_customer_must_be_at_least_one(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Coupon has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'perCustomerUsageLimit: Coupon usage limit per customer must be at least 1.');
    }
    #[Then('I should be notified that promotion is required')]
    public function i_should_be_notified_that_promotion_is_required(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Coupon has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'Parent resource not found.');
    }
    #[Then('I should be notified that only coupon based promotions can have coupons')]
    public function i_should_be_notified_that_only_coupon_based_promotions_can_have_coupons(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Coupon has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'promotion: Only coupon based promotions can have coupons.');
    }
    #[Then('I should be notified that generating :amount coupons with code length equal to :codeLength is not possible')]
    public function i_should_be_notified_that_generating_coupons_with_code_length_is_not_possible(int $amount, int $code_length): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Invalid coupon code length or coupons amount. It is not possible to generate %d unique coupons with %d code length', $amount, $code_length));
    }
    #[Then('I should be notified that generate amount is required')]
    public function i_should_be_notified_that_generate_amount_is_required(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'amount: Please enter amount of coupons to generate.');
    }
    #[Then('I should be notified that generate code length is required')]
    public function i_should_be_notified_that_code_length_is_required(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'codeLength: Please enter coupon code length.');
    }
    #[Then('I should be notified that generate code length is out of range')]
    public function i_should_be_notified_that_code_length_is_out_of_range(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'codeLength: Coupon code length must be between 1 and 40.');
    }
    #[Then('I should be notified that they have been successfully generated')]
    public function i_should_be_notified_that_they_have_been_successfully_generated(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Promotion coupon could not be generated');
    }
    #[Then('I should be notified that coupon with this code already exists')]
    public function i_should_be_notified_that_coupon_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Coupon has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: This coupon already exists.');
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'));
    }
    #[Then('/^("[^"]+" coupon) should be used (\d+) time(?:|s)$/')]
    public function coupon_should_have_usage_limit(Promotion_Coupon_Interface $promotion_coupon, int $used): void
    {
        $returned_promotion_coupon = current($this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'code', $promotion_coupon->get_code()));
        Assert::same($returned_promotion_coupon['used'], $used, sprintf('The promotion coupon %s has been used %s times', $promotion_coupon->get_code(), $returned_promotion_coupon['used']));
    }
    #[Then('I should see a single promotion coupon in the list')]
    public function i_should_see_a_single_promotion_coupon_in_the_list(): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), 1);
    }
    #[Then('I should see the promotion coupon :coupon in the list')]
    public function i_should_see_the_promotion_coupon_in_the_list(Promotion_Coupon_Interface $coupon): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'code', $coupon->get_code()));
    }
    private function sort_by(string $order, string $field): void
    {
        $this->client->sort([$field => str_starts_with($order, 'de') ? 'desc' : 'asc']);
    }
}