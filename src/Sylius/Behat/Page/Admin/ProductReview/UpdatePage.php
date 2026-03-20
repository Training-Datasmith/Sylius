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
namespace Sylius\Behat\Page\Admin\Product_Review;

use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    public function specify_title(string $title): void
    {
        $this->get_element('title')->set_value($title);
    }
    public function specify_comment(string $comment): void
    {
        $this->get_element('comment')->set_value($comment);
    }
    public function choose_rating(string $rating): void
    {
        $this->get_element('rating', ['%value%' => $rating])->get_parent()->click();
    }
    public function get_rating(): string
    {
        return $this->get_element('checked_rating')->get_value();
    }
    public function get_product_name(): string
    {
        return $this->get_element('product_name')->get_text();
    }
    public function get_customer_name(): string
    {
        return $this->get_element('author_name')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['author_name' => '[data-test-author-name]', 'checked_rating' => 'input[checked]', 'comment' => '[data-test-comment]', 'product_name' => '[data-test-product-name]', 'rating' => '[data-test-rating="%value%"]', 'title' => '[data-test-title]']);
    }
}