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

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Index_Page_Interface extends Page_Interface
{
    public function count_reviews(): int;
    public function has_review_titled(string $title): bool;
    public function has_no_reviews_message(): bool;
}