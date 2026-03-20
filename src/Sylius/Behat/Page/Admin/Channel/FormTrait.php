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

trait Form_Trait
{
    public function get_defined_form_elements(): array
    {
        return ['base_currency' => '[data-test-base-currency]', 'code' => '[data-test-code]', 'color' => '[data-test-color]', 'contact_email' => '[data-test-contact-email]', 'contact_phone_number' => '[data-test-contact-phone-number]', 'countries' => '#sylius_admin_channel_countries', 'currencies' => '#sylius_admin_channel_currencies', 'default_locale' => '#sylius_admin_channel_defaultLocale', 'default_tax_zone' => '[data-test-default-tax-zone]', 'discounted_products_checking_period' => '[data-test-lowest-price-for-discounted-products-checking-period]', 'enabled' => '[data-test-enabled]', 'hostname' => '[data-test-hostname]', 'locales' => '[data-test-locales]', 'menu_taxon' => '[data-test-menu-taxon]', 'name' => '[data-test-name]', 'tax_calculation_strategy' => '[data-test-tax-calculation-strategy]', 'theme' => '[data-test-theme]'];
    }
    public function set_hostname(string $hostname): void
    {
        $this->get_element('hostname')->set_value($hostname);
    }
    public function set_theme(string $theme_name): void
    {
        $this->get_element('theme')->select_option($theme_name);
    }
    public function get_theme(): string
    {
        return $this->get_element('theme')->get_value();
    }
    public function set_contact_email(string $contact_email): void
    {
        $this->get_element('contact_email')->set_value($contact_email);
    }
    public function set_contact_phone_number(string $contact_phone_number): void
    {
        $this->get_element('contact_phone_number')->set_value($contact_phone_number);
    }
    public function define_color(string $color): void
    {
        $this->get_element('color')->set_value($color);
    }
    public function choose_currency(string $currency_name): void
    {
        $this->get_element('currencies')->select_option($currency_name, true);
    }
    public function choose_locale(string $language): void
    {
        $this->get_element('locales')->select_option($language);
    }
    public function choose_default_tax_zone(string $tax_zone): void
    {
        $this->get_element('default_tax_zone')->select_option($tax_zone);
    }
    public function choose_default_locale(string $locale): void
    {
        $this->get_element('default_locale')->select_option($locale);
    }
    public function choose_operating_countries(array $countries): void
    {
        foreach ($countries as $country) {
            $this->get_element('countries')->select_option($country, true);
        }
    }
    public function choose_base_currency(string $currency): void
    {
        $this->get_element('currencies')->select_option($currency, true);
        $this->get_element('base_currency')->select_option($currency);
    }
    public function get_menu_taxon(): string
    {
        return $this->get_selected_option_text('menu_taxon');
    }
    public function specify_menu_taxon(string $menu_taxon): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('menu_taxon')->get_xpath(), $menu_taxon);
        $this->wait_for_form_update();
    }
    public function get_tax_calculation_strategy(): string
    {
        return $this->get_selected_option_text('tax_calculation_strategy');
    }
    public function choose_tax_calculation_strategy(string $tax_calculation_strategy): void
    {
        $this->get_element('tax_calculation_strategy')->select_option($tax_calculation_strategy);
    }
    protected function get_selected_option_text(string $element): string
    {
        return $this->get_element($element)->find('css', 'option:selected')->get_text();
    }
}