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

use Sylius\Behat\Page\Sylius_Page;
class Index_Page extends Sylius_Page implements Index_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_product_review_index';
    }
    public function count_reviews(): int
    {
        return count($this->get_element('reviews')->find_all('css', '[data-test-comment]'));
    }
    public function has_review_titled(string $title): bool
    {
        return $this->has_element('title', ['%title%' => $title]);
    }
    public function has_no_reviews_message(): bool
    {
        $reviews_container_text = $this->get_element('reviews')->get_text();
        return str_contains($reviews_container_text, 'There are no reviews');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['reviews' => '[data-test-product-reviews]', 'title' => '[data-test-product-reviews] [data-test-title="%title%"]']);
    }
}