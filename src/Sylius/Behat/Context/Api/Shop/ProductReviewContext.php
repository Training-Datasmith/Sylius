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
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Product\Model\Product_Interface;
use Sylius\Component\Review\Model\Review_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Review_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('I check this product\'s reviews')]
    public function i_check_this_products_reviews(): void
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $this->client->index(Resources::PRODUCT_REVIEWS);
        $this->client->add_filter('reviewSubject', $this->iri_converter->get_iri_from_resource($product));
        $this->client->filter();
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I want to review product :product')]
    public function i_want_to_review_product(Product_Interface $product): void
    {
        $this->client->build_create_request(Resources::PRODUCT_REVIEWS);
        $this->client->add_request_data('product', $this->iri_converter->get_iri_from_resource($product));
    }
    #[When('I leave a comment :comment as :email')]
    #[When('I leave a comment :comment, titled :title as :email')]
    #[When('I leave a comment :comment, titled :title')]
    #[When('I leave a review titled :title as :email')]
    public function i_leave_a_comment_titled(?string $comment = null, ?string $title = null, ?string $email = null): void
    {
        $this->client->add_request_data('title', $title);
        $this->client->add_request_data('comment', $comment);
        if (null !== $email) {
            $this->client->add_request_data('email', $email);
        }
    }
    #[When('I rate it with :rating point(s)')]
    #[When('I do not rate it')]
    public function i_rate_it_with_points(?int $rating = null): void
    {
        $this->client->add_request_data('rating', $rating);
    }
    #[When('I title it with very long title')]
    public function i_title_it_with_very_long_title(): void
    {
        $this->client->add_request_data('title', 'Exegi monumentum aere perennius regalique situ pyramidum altius, quod non imber edax, non Aquilo inpotens possit diruere aut innumerabilis annorum series et fuga temporum. Non omnis moriar multaque pars mei vitabit Libitinam; usque ego postera crescam laude recens, dum Capitoliumscandet cum tacita virgine pontifex.Dicar, qua violens obstrepit Aufiduset qua pauper aquae Daunus agrestiumregnavit populorum, ex humili potensprinceps Aeolium carmen ad Italosdeduxisse modos. Sume superbiamquaesitam meritis et mihi Delphicalauro cinge volens, Melpomene, comam.');
    }
    #[Then('I should see :amount product reviews')]
    public function i_should_see_product_reviews(int $amount = 0): void
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $this->client->index(Resources::PRODUCT_REVIEWS);
        $this->client->add_filter('reviewSubject', $this->iri_converter->get_iri_from_resource($product));
        $this->client->add_filter('itemsPerPage', 3);
        $this->client->add_filter('order[createdAt]', 'desc');
        $this->client->filter();
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $amount);
    }
    #[Then('I should see reviews titled :titleOne, :titleTwo and :titleThree')]
    public function i_should_see_reviews_titled_and(string ...$titles): void
    {
        Assert::true($this->has_reviews_with_titles($titles));
    }
    #[Then('I should not see review titled :title')]
    public function i_should_not_see_review_titled(string $title): void
    {
        Assert::false($this->has_reviews_with_titles([$title]));
    }
    #[Then('I should be notified that my review is waiting for the acceptation')]
    public function i_should_be_notified_that_my_review_is_waiting_for_the_acceptation(): void
    {
        // Intentionally left blank
    }
    #[Then('I should see :amount product reviews in the list')]
    #[Then('I should be notified that there are no reviews')]
    public function i_should_see_product_reviews_in_the_list(int $amount = 0): void
    {
        $product_reviews = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::count($product_reviews, $amount);
    }
    #[Then('I should not see review titled :title in the list')]
    public function i_should_not_see_review_titled_in_the_list(string $title): void
    {
        Assert::is_empty($this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'title', $title));
    }
    #[Then('I should be notified that I must check review rating')]
    public function i_should_be_notified_that_i_must_check_review_rating(): void
    {
        $this->assert_error('Request field "rating" should be of type "int".');
    }
    #[Then('I should be notified that title is required')]
    public function i_should_be_notified_that_title_is_required(): void
    {
        $this->assert_error('Request field "title" should be of type "string".');
    }
    #[Then('I should be notified that title must have at least 2 characters')]
    public function i_should_be_notified_that_title_must_have_at_least2characters(): void
    {
        $this->assert_violation('Review title must have at least 2 characters.', 'title');
    }
    #[Then('I should be notified that title must have at most 255 characters')]
    public function i_should_be_notified_that_title_must_have_at_most255characters(): void
    {
        $this->assert_violation('Review title must have at most 255 characters.', 'title');
    }
    #[Then('I should be notified that comment is required')]
    public function i_should_be_notified_that_comment_is_required(): void
    {
        $this->assert_error('Request field "comment" should be of type "string".');
    }
    #[Then('I should be notified that I must enter my email')]
    public function i_should_be_notified_that_i_must_enter_my_email(): void
    {
        $this->assert_violation('Please enter your email.', 'email');
    }
    #[Then('I should be notified that this email is already registered')]
    public function i_should_be_notified_that_this_email_is_already_registered(): void
    {
        $this->assert_violation('This email is already registered, please login or use forgotten password.', 'email');
    }
    #[Then('I should be notified that rating must be between 1 and 5')]
    public function i_should_be_notified_that_rating_must_be_between1and5(): void
    {
        $this->assert_violation('Review rating must be between 1 and 5.', 'rating');
    }
    #[Then('the :productReview product review of :product product should not be visible for customers')]
    public function this_product_review_of_product_should_not_be_visible_for_customers(Review_Interface $product_review, Product_Interface $product): void
    {
        $this->client->index(Resources::PRODUCT_REVIEWS);
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'title', $product_review->get_title()), sprintf('Product review with title "%s" should not be visible for customers', $product_review->get_title()));
    }
    private function has_reviews_with_titles(array $titles): bool
    {
        foreach ($titles as $title) {
            if (!$this->response_checker->has_item_with_value($this->client->get_last_response(), 'title', $title)) {
                return false;
            }
        }
        return true;
    }
    private function assert_violation(string $message, ?string $property = null): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 422);
        Assert::true($this->response_checker->has_violation_with_message($response, $message, $property));
    }
    private function assert_error(string $error): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 400);
        Assert::same($this->response_checker->get_error($response), $error);
    }
}