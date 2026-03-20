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

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
use Sylius\Component\Core\Model\Channel_Interface;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Specifies_Its_Field;
    public function specify_price(string $price, Channel_Interface $channel): void
    {
        $this->get_element('price', ['%channel_code%' => $channel->get_code()])->set_value($price);
    }
    public function specify_minimum_price(string $price, Channel_Interface $channel): void
    {
        $this->get_element('minimum_price', ['%channel_code%' => $channel->get_code()])->set_value($price);
    }
    public function specify_original_price(string $original_price, Channel_Interface $channel): void
    {
        $this->get_element('original_price', ['%channel_code%' => $channel->get_code()])->set_value($original_price);
    }
    public function specify_current_stock(string $current_stock): void
    {
        $this->get_document()->fill_field('Current stock', $current_stock);
    }
    public function specify_height_width_depth_and_weight(string $height, string $width, string $depth, string $weight): void
    {
        $this->get_document()->fill_field('Height', $height);
        $this->get_document()->fill_field('Width', $width);
        $this->get_document()->fill_field('Depth', $depth);
        $this->get_document()->fill_field('Weight', $weight);
    }
    public function name_it_in(string $name, string $language): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_product_variant_translations_%s_name', $language), $name);
    }
    public function select_option(string $option_name, string $option_value): void
    {
        $option_name = strtoupper($option_name);
        $this->get_element('option_select', ['%option-name%' => $option_name])->select_option($option_value);
    }
    public function choose_pricing_calculator(string $name): void
    {
        $this->get_element('price_calculator')->select_option($name);
    }
    public function get_validation_message_for_form(): string
    {
        $validation_message = $this->get_document()->find('css', '.alert.alert-danger.d-block');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.alert.alert-danger.d-block');
        }
        return $validation_message->get_text();
    }
    public function select_shipping_category(string $shipping_category_name): void
    {
        $this->get_element('shipping_category')->select_option($shipping_category_name);
    }
    public function get_prices_validation_message(): string
    {
        return $this->get_element('prices-body')->get_text();
    }
    public function set_shipping_required(bool $is_shipping_required): void
    {
        if ($is_shipping_required) {
            $this->get_element('shipping_required')->check();
            return;
        }
        $this->get_element('shipping_required')->uncheck();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '#sylius_admin_product_variant_code', 'depth' => '#sylius_admin_product_variant_depth', 'height' => '#sylius_admin_product_variant_height', 'minimum_price' => '#sylius_admin_product_variant_channelPricings_%channel_code%_minimumPrice', 'on_hand' => '#sylius_admin_product_variant_onHand', 'option_select' => '#sylius_admin_product_variant_optionValues_%option-name%', 'original_price' => '#sylius_admin_product_variant_channelPricings_%channel_code%_originalPrice', 'price' => '#sylius_admin_product_variant_channelPricings_%channel_code%_price', 'price_calculator' => '#sylius_admin_product_variant_pricingCalculator', 'prices-body' => '[data-test-product-channel-pricings-accordion-body]', 'shipping_category' => '#sylius_admin_product_variant_shippingCategory', 'shipping_required' => '#sylius_admin_product_variant_shippingRequired', 'weight' => '#sylius_admin_product_variant_weight', 'width' => '#sylius_admin_product_variant_width']);
    }
}