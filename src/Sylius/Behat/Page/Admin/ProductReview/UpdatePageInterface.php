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

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function specify_title(string $title): void;
    public function specify_comment(string $comment): void;
    public function choose_rating(string $rating): void;
    public function get_rating(): string;
    public function get_product_name(): string;
    public function get_customer_name(): string;
}