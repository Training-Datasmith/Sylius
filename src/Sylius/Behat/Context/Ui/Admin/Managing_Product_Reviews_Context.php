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
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Product_Review\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Review\Update_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Review\Model\Review_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Reviews_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('I am browsing product reviews')]
    #[When('I browse product reviews')]
    #[When('I want to browse product reviews')]
    public function i_want_to_browse_product_reviews(): void
    {
        $this->index_page->open();
    }
    #[When('I check (also) the :productReviewTitle product review')]
    public function i_check_the_product_review(string $product_review_title): void
    {
        $this->index_page->check_resource_on_page(['title' => $product_review_title]);
    }
    #[When('I choose :state as a status filter')]
    public function i_choose_state_as_status_filter(string $state): void
    {
        $this->index_page->filter_by_state($state);
    }
    #[When('I filter with title containing :phrase')]
    public function i_filter_with_title_containing(string $phrase): void
    {
        $this->index_page->filter_by_title($phrase);
        $this->index_page->filter();
    }
    #[When('I filter by :productName product')]
    public function i_filter_by_product(string $product_name): void
    {
        $this->index_page->filter_by_product($product_name);
        $this->index_page->filter();
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[When('I sort the product reviews :sortingOrder by :field')]
    public function i_sort_product_reviews_by(string $sorting_order, string $field): void
    {
        $this->index_page->sort_by($field, $sorting_order === 'descending' ? 'desc' : 'asc');
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('I want to modify the :productReview product review')]
    public function i_want_to_modify_the_product_review(Review_Interface $product_review): void
    {
        $this->update_page->open(['id' => $product_review->get_id()]);
    }
    #[When('I change its title to :title')]
    #[When('I remove its title')]
    public function i_change_its_title_to(?string $title = null): void
    {
        $this->update_page->specify_title($title ?? '');
    }
    #[When('I change its comment to :comment')]
    #[When('I remove its comment')]
    public function i_change_its_comment_to(?string $comment = null): void
    {
        $this->update_page->specify_comment($comment ?? '');
    }
    #[When('I choose :rating as its rating')]
    public function i_choose_as_its_rating(string $rating): void
    {
        $this->update_page->choose_rating($rating);
    }
    #[When('I accept the :productReview product review')]
    public function i_accept_the_product_review(Review_Interface $product_review): void
    {
        $this->index_page->accept(['title' => $product_review->get_title()]);
    }
    #[When('I reject the :productReview product review')]
    public function i_reject_the_product_review(Review_Interface $product_review): void
    {
        $this->index_page->reject(['title' => $product_review->get_title()]);
    }
    #[Then('I should (also) see the product review :title in the list')]
    public function i_should_see_the_product_review_title_in_the_list(string $title): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['title' => $title]));
    }
    #[Then('I should see a single product review in the list')]
    #[Then('I should see :amount reviews in the list')]
    public function i_should_see_reviews_in_the_list(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    #[Then('/^this product review (comment|title) should be "([^"]+)"$/')]
    public function this_product_review_element_should_be_value(string $element, string $value): void
    {
        $this->assert_element_value($element, $value);
    }
    #[Then('this product review rating should be :rating')]
    public function this_product_review_rating_should_be(string $rating): void
    {
        Assert::same($this->update_page->get_rating(), $rating);
    }
    #[Then('I should be editing review of product :productName')]
    public function i_should_be_editing_review_of_product(string $product_name): void
    {
        Assert::same($this->update_page->get_product_name(), $product_name);
    }
    #[Then('I should see the customer\'s name :customerName')]
    public function i_should_see_the_customer_s_name(string $customer_name): void
    {
        Assert::same($this->update_page->get_customer_name(), $customer_name);
    }
    #[Then('/^(this product review) status should be "([^"]+)"$/')]
    public function this_product_review_status_should_be(Review_Interface $product_review, string $status): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['title' => $product_review->get_title(), 'status' => $status]));
    }
    #[Then('/^I should be notified that it has been successfully (accepted|rejected)$/')]
    public function i_should_be_notified_that_it_has_been_successfully_updated(string $action): void
    {
        $this->notification_checker->check_notification(sprintf('Review has been successfully %s.', $action), Notification_Type::success());
    }
    #[When('I delete the :productReview product review')]
    public function i_delete_the_product_review(Review_Interface $product_review): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['title' => $product_review->get_title()]);
    }
    #[Then('/^(this product review) should no longer exist in the registry$/')]
    public function this_product_review_should_no_longer_exist_in_the_registry(Review_Interface $product_review): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['title' => $product_review->get_title()]));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Review %s should not be blank.', $element));
    }
    #[Then('/^this product review should still be titled "([^"]+)"$/')]
    public function this_product_review_title_should_be_titled(string $product_review_title): void
    {
        $this->i_want_to_browse_product_reviews();
        Assert::true($this->index_page->is_single_resource_on_page(['title' => $product_review_title]));
    }
    #[Then('/^(this product review) should still have a comment "([^"]+)"$/')]
    public function this_product_review_should_still_have_a_comment(Review_Interface $product_review, string $comment): void
    {
        $this->i_want_to_modify_the_product_review($product_review);
        $this->assert_element_value('comment', $comment);
    }
    #[Then('the first product review in the list should have title :title')]
    public function the_first_product_review_in_the_list_should_have_title(string $title): void
    {
        $titles = $this->index_page->get_column_fields('title');
        Assert::contains(reset($titles), $title);
    }
    #[Then('the last product review in the list should have title :title')]
    public function the_last_product_review_in_the_list_should_have_title(string $title): void
    {
        $titles = $this->index_page->get_column_fields('title');
        Assert::contains(end($titles), $title);
    }
    private function assert_element_value(string $element, string $value): void
    {
        Assert::true($this->update_page->has_resource_values([$element => $value]));
    }
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        Assert::same($this->update_page->get_validation_message($element), $expected_message);
    }
}