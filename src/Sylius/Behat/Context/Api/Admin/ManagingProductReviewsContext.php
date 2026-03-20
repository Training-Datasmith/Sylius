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
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Review\Model\Review_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Reviews_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('I am browsing product reviews')]
    #[When('I (want to) browse product reviews')]
    public function i_want_to_browse_product_reviews(): void
    {
        $this->client->index(Resources::PRODUCT_REVIEWS);
    }
    #[When('I choose :status as a status filter')]
    public function i_choose_as_status_filter(string $status): void
    {
        $this->client->add_filter('status', $status);
    }
    #[When('I filter with title containing :title')]
    public function i_filter_with_title_containing(string $title): void
    {
        $this->client->add_filter('title', $title);
        $this->client->filter();
    }
    #[When('I filter by :product product')]
    public function i_filter_by_product(Product_Interface $product): void
    {
        $this->client->add_filter('reviewSubject', $this->iri_converter->get_iri_from_resource_in_section($product, 'admin'));
        $this->client->filter();
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[When('I sort the product reviews :sortingOrder by :field')]
    public function i_sort_product_reviews_by(string $sorting_order, string $field): void
    {
        $field = $field === 'date' ? 'createdAt' : $field;
        $this->client->sort([$field => $sorting_order === 'descending' ? 'desc' : 'asc']);
    }
    #[When('I want to modify the :productReview product review')]
    public function i_want_to_modify_the_product_review(Review_Interface $product_review): void
    {
        $this->client->build_update_request(Resources::PRODUCT_REVIEWS, (string) $product_review->get_id());
    }
    #[When('I change its title to :title')]
    #[When('I remove its title')]
    public function i_change_its_title_to(?string $title = ''): void
    {
        $this->client->add_request_data('title', $title);
    }
    #[When('I change its comment to :comment')]
    #[When('I remove its comment')]
    public function i_change_its_comment_to(?string $comment = ''): void
    {
        $this->client->update_request_data(['comment' => $comment]);
    }
    #[When('I choose :rating as its rating')]
    public function i_choose_as_its_rating(int $rating): void
    {
        $this->client->update_request_data(['rating' => $rating]);
    }
    #[When('/^I (accept|reject) the ("([^"]+)" product review)$/')]
    public function i_change_state_the_product_review(string $state, Review_Interface $product_review): void
    {
        $this->client->apply_transition(Resources::PRODUCT_REVIEWS, (string) $product_review->get_id(), $state);
    }
    #[When('I delete the :productReview product review')]
    public function i_delete_the_product_review(Review_Interface $product_review): void
    {
        $this->shared_storage->set('product_review_id', $product_review->get_id());
        $this->client->delete(Resources::PRODUCT_REVIEWS, (string) $product_review->get_id());
    }
    #[Then('I should (also) see the product review :title in the list')]
    public function i_should_see_the_product_review_title_in_the_list(string $title): void
    {
        Assert::true($this->is_item_on_index('title', $title), sprintf('Product review with title %s does not exist', $title));
    }
    #[Then('I should see a single product review in the list')]
    #[Then('I should see :amount reviews in the list')]
    public function i_should_see_reviews_in_the_list(int $amount = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $amount);
    }
    #[Then('/^(this product review) (comment|title) should be "([^"]+)"$/')]
    public function this_product_review_element_should_be_value(Review_Interface $product_review, string $element, string $value): void
    {
        $this->assert_if_review_has_element_with_value($product_review, $element, $value);
    }
    #[Then('/^(this product review) rating should be (\d+)$/')]
    public function this_product_review_rating_should_be(Review_Interface $product_review, int $rating): void
    {
        $this->assert_if_review_has_element_with_value($product_review, 'rating', $rating);
    }
    #[Then('/^(this product review) status should be "([^"]+)"$/')]
    public function this_product_review_status_should_be(Review_Interface $product_review, string $status): void
    {
        $this->assert_if_review_has_element_with_value($product_review, 'status', $status);
    }
    #[Then('/^I should be notified that it has been successfully (accepted|rejected)$/')]
    public function i_should_be_notified_that_it_has_been_successfully_updated(string $action): void
    {
        $this->assert_if_review_has_element_with_value($this->shared_storage->get('product_review'), 'status', $action);
    }
    #[Then('this product review should no longer exist in the registry')]
    public function this_product_review_should_no_longer_exist_in_the_registry(): void
    {
        $id = (string) $this->shared_storage->get('product_review_id');
        Assert::false($this->is_item_on_index('id', $id), sprintf('Product review with id %s exist', $id));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Review %s should not be blank', $element, $element));
    }
    #[Then('/^(this product review) should still be titled "([^"]+)"$/')]
    public function this_product_review_title_should_be_titled(Review_Interface $product_review, string $title): void
    {
        $this->assert_if_review_has_element_with_value($product_review, 'title', $title);
    }
    #[Then('/^(this product review) should still have a comment "([^"]+)"$/')]
    public function this_product_review_should_still_have_a_comment(Review_Interface $product_review, string $comment): void
    {
        $this->assert_if_review_has_element_with_value($product_review, 'comment', $comment);
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Product review could not be deleted');
    }
    #[Then('average rating of product :product should be :expectedRating')]
    public function average_rating_of_product_should_be(Product_Interface $product, int $expected_rating): void
    {
        $average_rating = $this->response_checker->get_value($this->client->show(Resources::PRODUCTS, (string) $product->get_code()), 'averageRating');
        Assert::same($average_rating, $expected_rating, sprintf('Average rating of product %s is not %s', $product->get_name(), $expected_rating));
    }
    #[Then('/^the (first|last) product review in the list should have title "([^"]+)"$/')]
    public function the_nth_product_review_in_the_list_should_have_title(string $nth, string $title): void
    {
        $reviews = $this->response_checker->get_collection($this->client->get_last_response());
        $review = 'first' === $nth ? reset($reviews) : end($reviews);
        Assert::same($review['title'], $title);
    }
    private function is_item_on_index(string $property, string $value): bool
    {
        return $this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_REVIEWS), $property, $value);
    }
    private function assert_if_review_has_element_with_value(Review_Interface $product_review, string $element, int|string $value): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PRODUCT_REVIEWS, (string) $product_review->get_id()), $element, $value), sprintf('Product review %s is not %s', $element, $value));
    }
}