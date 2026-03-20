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
interface Create_Page_Interface extends Page_Interface
{
    public function title_review(?string $title): void;
    public function set_comment(?string $comment): void;
    public function set_author(string $author): void;
    public function rate_review(int $rate): void;
    public function submit_review(): void;
    public function get_rate_validation_message(): string;
    public function get_title_validation_message(): string;
    public function get_comment_validation_message(): string;
    public function get_author_validation_message(): string;
}