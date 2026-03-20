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
namespace Sylius\Behat\Element\Admin\Product;

use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Component\Core\Model\Channel_Interface;
class Channel_Pricings_Form_Element extends Base_Form_Element implements Channel_Pricings_Form_Element_Interface
{
    public function specify_price(Channel_Interface $channel, string $price): void
    {
        $this->change_tab();
        $this->change_channel_accordion($channel->get_code());
        $this->get_element('price', ['%channel_code%' => $channel->get_code()])->set_value($price);
    }
    public function specify_original_price(Channel_Interface $channel, int $original_price): void
    {
        $this->change_tab();
        $this->change_channel_accordion($channel->get_code());
        $this->get_element('original_price', ['%channel_code%' => $channel->get_code()])->set_value($original_price);
    }
    public function get_price_for_channel(Channel_Interface $channel): string
    {
        return $this->get_element('price', ['%channel_code%' => $channel->get_code()])->get_value();
    }
    public function get_original_price_for_channel(Channel_Interface $channel): string
    {
        return $this->get_element('original_price', ['%channel_code%' => $channel->get_code()])->get_value();
    }
    public function has_no_price_for_channel(string $channel_name): bool
    {
        return !str_contains($this->get_element('channels')->get_text(), $channel_name);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel' => '[data-test-channel-code="%channel_code%"]', 'channel_accordion' => '[data-test-product-channel-pricings-accordion="%channel_code%"]', 'channels' => '[data-test-channels]', 'original_price' => '[data-test-original-price-in-channel="%channel_code%"]', 'price' => '[data-test-price-in-channel="%channel_code%"]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
    protected function change_channel_accordion(string $channel_code): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $accordion = $this->get_element('channel_accordion', ['%channel_code%' => $channel_code]);
        if ($accordion->has_class('collapsed')) {
            $accordion->click();
        }
    }
    protected function change_tab(): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => 'channel-pricing'])->click();
    }
}