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

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function enable(): void;
    public function disable(): void;
    public function get_theme(): string;
    public function set_theme(string $theme_name): void;
    /** @return string[] */
    public function get_locales(): array;
    public function choose_locale(string $language): void;
    /** @return string[] */
    public function get_currencies(): array;
    public function choose_currency(string $currency_code): void;
    public function get_default_tax_zone(): ?string;
    public function choose_default_tax_zone(string $tax_zone): void;
    public function get_tax_calculation_strategy(): string;
    public function choose_tax_calculation_strategy(string $tax_calculation_strategy): void;
    public function is_code_disabled(): bool;
    public function is_base_currency_disabled(): bool;
    public function specify_menu_taxon(string $menu_taxon): void;
    public function get_menu_taxon(): string;
}