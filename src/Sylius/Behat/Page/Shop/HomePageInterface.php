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
namespace Sylius\Behat\Page\Shop;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Home_Page_Interface extends Sylius_Page_Interface
{
    public function get_content(): string;
    public function has_logout_button(): bool;
    public function log_out();
    public function get_full_name(): string;
    public function get_active_currency(): string;
    public function get_available_currencies(): array;
    public function switch_currency(string $currency_code): void;
    public function get_active_locale(): string;
    public function get_available_locales(): array;
    public function switch_locale(string $locale_code): void;
    public function get_latest_products_names(): array;
    public function get_latest_deals_names(): array;
}