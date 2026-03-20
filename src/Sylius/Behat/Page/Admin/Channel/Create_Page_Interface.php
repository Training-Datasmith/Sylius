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
namespace Sylius\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
interface Create_Page_Interface extends Base_Create_Page_Interface
{
    public function enable(): void;
    public function disable(): void;
    public function name_it(string $name): void;
    public function specify_code(string $code): void;
    public function describe_it_as(string $description): void;
    public function set_hostname(string $hostname): void;
    public function set_contact_email(string $contact_email): void;
    public function set_contact_phone_number(string $contact_phone_number): void;
    public function define_color(string $color): void;
    public function choose_locale(string $language): void;
    public function choose_currency(string $currency_name): void;
    public function choose_default_tax_zone(string $tax_zone): void;
    public function choose_default_locale(string $locale): void;
    /** @param string[] $countries */
    public function choose_operating_countries(array $countries): void;
    public function choose_base_currency(string $currency): void;
    public function choose_tax_calculation_strategy(string $tax_calculation_strategy): void;
    public function allow_to_skip_shipping_step(): void;
    public function allow_to_skip_payment_step(): void;
    public function specify_menu_taxon(string $menu_taxon): void;
}