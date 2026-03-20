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
namespace Sylius\Behat\Page;

interface Error_Page_Interface
{
    public function get_code(): int;
    public function is_it_admin_not_found_page(): bool;
    public function is_it_shop_not_found_page(): bool;
}