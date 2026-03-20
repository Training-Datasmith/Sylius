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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Product_Review\Create_Page_Interface;
use Sylius\Behat\Page\Shop\Product_Review\Index_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Review\Model\Review_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Review_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Notification_Checker_Interface $notification_checker, private Index_Page_Interface $index_page)
    {
    }
    #[When('I want to review product :product')]
    public function i_want_to_review_product(Product_Interface $product): void
    {
        $this->create_page->open(['slug' => $product->get_slug()]);
    }
    #[When('I leave a comment :comment as :author')]
    #[When('I leave a comment :comment, titled :title')]
    #[When('I leave a comment :comment, titled :title as :author')]
    #[When('I leave a review titled :title as :author')]
    public function i_leave_a_comment_titled(?string $comment = null, ?string $title = null, ?string $author = null): void
    {
        $this->create_page->title_review($title);
        $this->create_page->set_comment($comment);
        if (null !== $author) {
            $this->create_page->set_author($author);
        }
    }
    #[When('I title it with very long title')]
    public function i_title_it_with_very_long_title(): void
    {
        $this->create_page->title_review($this->get_very_long_title());
    }
    #[When('I rate it with :rate point(s)')]
    public function i_rate_it_with_points(int $rate): void
    {
        $this->create_page->rate_review($rate);
    }
    #[When('I do not rate it')]
    public function i_do_not_rate_it(): void
    {
        // intentionally left blank, as review rate is not selected by default
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->submit_review();
    }
    #[Then('I should be notified that my review is waiting for the acceptation')]
    public function i_should_be_notified_that_my_review_is_waiting_for_the_acceptation(): void
    {
        $this->notification_checker->check_notification('Your review is waiting for the acceptation.', Notification_Type::success());
    }
    #[Then('I should be notified that I must check review rating')]
    public function i_should_be_notified_that_i_must_check_review_rating(): void
    {
        Assert::same($this->create_page->get_rate_validation_message(), 'You must check review rating.');
    }
    #[Then('I should be notified that title is required')]
    public function i_should_be_notified_that_title_is_required(): void
    {
        Assert::same($this->create_page->get_title_validation_message(), 'Review title should not be blank.');
    }
    #[Then('I should be notified that title must have at least 2 characters')]
    public function i_should_be_notified_that_title_must_have_at_least2characters(): void
    {
        Assert::same($this->create_page->get_title_validation_message(), 'Review title must have at least 2 characters.');
    }
    #[Then('I should be notified that title must have at most 255 characters')]
    public function i_should_be_notified_that_title_must_have_at_most255characters(): void
    {
        Assert::same($this->create_page->get_title_validation_message(), 'Review title must have at most 255 characters.');
    }
    #[Then('I should be notified that comment is required')]
    public function i_should_be_notified_that_comment_is_required(): void
    {
        Assert::same($this->create_page->get_comment_validation_message(), 'Review comment should not be blank.');
    }
    #[Then('I should be notified that I must enter my email')]
    public function i_should_be_notified_that_i_must_enter_my_email(): void
    {
        Assert::same($this->create_page->get_author_validation_message(), 'Please enter your email.');
    }
    #[Then('I should be notified that this email is already registered')]
    public function i_should_be_notified_that_this_email_is_already_registered(): void
    {
        Assert::same($this->create_page->get_author_validation_message(), 'This email is already registered, please login or use forgotten password.');
    }
    #[Then('the :productReview product review of :product product should not be visible for customers')]
    public function this_product_review_of_product_should_not_be_visible_for_customers(Review_Interface $product_review, Product_Interface $product): void
    {
        $this->index_page->open(['slug' => $product->get_slug()]);
        Assert::false($this->index_page->has_review_titled($product_review->get_title()));
    }
    private function get_very_long_title(): string
    {
        return 'Exegi monumentum aere perennius regalique situ pyramidum altius, quod non imber edax, non Aquilo inpotens possit diruere aut innumerabilis annorum series et fuga temporum. Non omnis moriar multaque pars mei vitabit Libitinam; usque ego postera crescam laude recens, dum Capitoliumscandet cum tacita virgine pontifex.Dicar, qua violens obstrepit Aufiduset qua pauper aquae Daunus agrestiumregnavit populorum, ex humili potensprinceps Aeolium carmen ad Italosdeduxisse modos. Sume superbiamquaesitam meritis et mihi Delphicalauro cinge volens, Melpomene, comam.';
    }
}