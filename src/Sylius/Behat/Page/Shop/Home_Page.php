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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Unsupported_Driver_Action_Exception;
use Sylius\Behat\Page\Sylius_Page;
class Home_Page extends Sylius_Page implements Home_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_homepage';
    }
    public function get_content(): string
    {
        return $this->get_document()->get_content();
    }
    public function log_out(): void
    {
        $this->get_element('logout_button')->click();
    }
    public function has_logout_button(): bool
    {
        return $this->has_element('logout_button');
    }
    public function get_full_name(): string
    {
        if ($this->has_element('full_name')) {
            return $this->get_element('full_name')->get_text();
        }
        return '';
    }
    public function get_active_currency(): string
    {
        return $this->get_element('active_currency')->get_text();
    }
    public function get_available_currencies(): array
    {
        return array_map(fn(Node_Element $element) => $element->get_text(), $this->get_element('currency_selector')->find_all('css', '[data-test-available-currency]'));
    }
    public function switch_currency(string $currency_code): void
    {
        try {
            $this->get_element('currency_selector')->click();
            // Needed for javascript scenarios
        } catch (Unsupported_Driver_Action_Exception) {
        }
        $this->get_element('currency_selector')->click_link($currency_code);
    }
    public function get_active_locale(): string
    {
        return $this->get_element('active_locale')->get_attribute('data-test-active-locale');
    }
    public function get_available_locales(): array
    {
        return array_map(fn(Node_Element $element) => $element->get_attribute('data-test-available-locale'), $this->get_element('locale_selector')->find_all('css', '[data-test-available-locale]'));
    }
    public function switch_locale(string $locale_code): void
    {
        $this->get_element('locale_selector')->find('css', sprintf('[data-test-available-locale="%s"]', $locale_code))->click();
    }
    public function get_latest_products_names(): array
    {
        return $this->get_products_names('latest_products');
    }
    public function get_latest_deals_names(): array
    {
        return $this->get_products_names('latest_deals');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['active_currency' => '[data-test-currency-selector] [data-test-active-currency]', 'active_locale' => '[data-test-locale-selector] [data-test-active-locale]', 'currency_selector' => '[data-test-currency-selector]', 'full_name' => '[data-test-full-name]', 'latest_deals' => '[data-test-latest-deals]', 'latest_products' => '[data-test-latest-products]', 'locale_selector' => '[data-test-locale-selector]', 'logout_button' => '[data-test-button="logout-button"]']);
    }
    protected function get_products_names(string $element_name): array
    {
        return array_map(fn(Node_Element $element) => $element->get_text(), $this->get_element($element_name)->find_all('css', '[data-test-product-name]'));
    }
}