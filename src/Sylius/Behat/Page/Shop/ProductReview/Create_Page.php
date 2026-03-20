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
namespace Sylius\Behat\Page\Shop\Product_Review;

use Sylius\Behat\Page\Shop\Page;
use Sylius\Behat\Service\Driver_Helper;
use Webmozart\Assert\Assert;
class Create_Page extends Page implements Create_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_product_review_create';
    }
    public function title_review(?string $title): void
    {
        $this->wait_for_element_update('title');
        $this->get_element('title')->set_value($title);
    }
    public function set_comment(?string $comment): void
    {
        $this->wait_for_element_update('comment');
        $this->get_element('comment')->set_value($comment);
    }
    public function set_author(string $author): void
    {
        $this->wait_for_element_update('author');
        $this->get_element('author')->set_value($author);
    }
    public function rate_review(int $rate): void
    {
        $this->wait_for_element_update('rating');
        $this->get_element('rating_option', ['%value%' => $rate])->get_parent()->click();
    }
    public function submit_review(): void
    {
        $this->wait_for_element_update('add');
        $this->get_element('add')->press();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function get_rate_validation_message(): string
    {
        return $this->get_validation_message_for('rating');
    }
    public function get_title_validation_message(): string
    {
        return $this->get_validation_message_for('title');
    }
    public function get_comment_validation_message(): string
    {
        return $this->get_validation_message_for('comment');
    }
    public function get_author_validation_message(): string
    {
        return $this->get_validation_message_for('author');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add' => '[data-test-add]', 'author' => '[data-test-author-email]', 'comment' => '[data-test-comment]', 'rating' => '[data-test-rating]', 'rating_option' => '[data-test-rating-option="%value%"]', 'title' => '[data-test-title]']);
    }
    protected function get_validation_message_for(string $element): string
    {
        $error_element = $this->get_element($element)->get_parent()->find('css', '[data-test-validation-error]');
        Assert::not_null($error_element);
        return $error_element->get_text();
    }
}