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
namespace Sylius\Behat\Page\Admin\Product_Variant;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Checks_Code_Immutability;
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    public function specify_price(int $price, ?Channel_Interface $channel = null): void
    {
        if ($channel === null) {
            $this->get_document()->fill_field('Price', $price);
            return;
        }
        $this->get_element('price', ['%channelCode%' => $channel->get_code()])->set_value($price);
    }
    public function specify_original_price(?int $original_price, ?Channel_Interface $channel = null): void
    {
        if ($channel === null) {
            $this->get_document()->fill_field('Original price', $original_price);
            return;
        }
        $this->get_element('original_price', ['%channelCode%' => $channel->get_code()])->set_value($original_price);
    }
    public function disable_tracking(): void
    {
        $this->get_element('tracked')->uncheck();
    }
    public function enable_tracking(): void
    {
        $this->get_element('tracked')->check();
    }
    public function is_tracked(): bool
    {
        return $this->get_element('tracked')->is_checked();
    }
    public function get_pricing_configuration_for_channel_and_currency_calculator(Channel_Interface $channel, Currency_Interface $currency): string
    {
        $price_element = $this->get_element('pricing_configuration')->find('css', sprintf('label:contains("%s %s")', $channel->get_code(), $currency->get_code()))->get_parent();
        return $price_element->find('css', 'input')->get_value();
    }
    public function get_price_for_channel(Channel_Interface $channel): string
    {
        return $this->get_element('price', ['%channelCode%' => $channel->get_code()])->get_value();
    }
    public function get_minimum_price_for_channel(Channel_Interface $channel): string
    {
        return $this->get_element('minimum_price', ['%channelCode%' => $channel->get_code()])->get_value();
    }
    public function get_original_price_for_channel(Channel_Interface $channel): string
    {
        return $this->get_element('original_price', ['%channelCode%' => $channel->get_code()])->get_value();
    }
    public function get_name_in_language(string $language): string
    {
        return $this->get_element('name', ['%language%' => $language])->get_value();
    }
    public function specify_current_stock(int $amount): void
    {
        $this->get_element('on_hand')->set_value($amount);
    }
    public function select_option(string $option_name, string $option_value): void
    {
        $this->get_element('option_values', ['%optionName%' => $option_name])->select_option($option_value);
    }
    public function is_show_in_shop_button_disabled(): bool
    {
        return $this->get_element('view_in_store')->has_class('disabled');
    }
    public function show_product_in_channel(Channel_Interface $channel): void
    {
        $this->get_element('view_in_store_in_channel', ['%channel_code%' => $channel->get_code()])->click();
    }
    public function show_product_in_single_channel(): void
    {
        $this->get_element('view_in_store')->click();
    }
    public function is_selected_option_value_on_page(string $option_name, string $value_name): bool
    {
        return $this->get_document()->find('css', sprintf('option:contains("%s")', $value_name))->is_selected();
    }
    public function is_shipping_required(): bool
    {
        return $this->get_element('shipping_required')->is_checked();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '#sylius_admin_product_variant_code', 'enabled' => '#sylius_admin_product_variant_enabled', 'minimum_price' => '#sylius_admin_product_variant_channelPricings_%channelCode%_minimumPrice', 'name' => '#sylius_admin_product_variant_translations_%language%_name', 'on_hand' => '#sylius_admin_product_variant_onHand', 'option_values' => '#sylius_admin_product_variant_optionValues_%optionName%', 'original_price' => '#sylius_admin_product_variant_channelPricings_%channelCode%_originalPrice', 'price' => '#sylius_admin_product_variant_channelPricings_%channelCode%_price', 'pricing_configuration' => '#sylius_calculator_container', 'shipping_required' => '#sylius_admin_product_variant_shippingRequired', 'show_product_single_button' => 'a:contains("Show product in shop page")', 'tracked' => '#sylius_admin_product_variant_tracked', 'view_in_store' => '[data-test-view-in-store]', 'view_in_store_in_channel' => '[data-test-view-in-store] [data-test-channel-code="%channel_code%"]']);
    }
    public function disable(): void
    {
        $this->get_element('enabled')->uncheck();
    }
    public function is_enabled(): bool
    {
        return $this->get_element('enabled')->is_checked();
    }
    public function enable(): void
    {
        $this->get_element('enabled')->check();
    }
}