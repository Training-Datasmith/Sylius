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

use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
use Sylius\Behat\Service\Tabs_Helper;
class Generate_Page extends Base_Create_Page implements Generate_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_admin_product_variant_generate';
    }
    public function specify_code(int $nth, string $code): void
    {
        $this->get_element('code', ['%position%' => $nth])->set_value($code);
    }
    public function specify_price(int $nth, int $price, string $channel_code): void
    {
        $channel_pricing = $this->get_element('channel_pricings', ['%position%' => $nth]);
        Tabs_Helper::switch_tab($this->get_session(), $channel_pricing, $channel_code);
        $channel_pricing->find('css', sprintf('[id$="_channelPricings_%s"]', $channel_code))->fill_field('Price', $price);
    }
    public function generate(): void
    {
        $this->get_element('generate_button')->press();
    }
    public function remove_variant(int $nth): void
    {
        $this->get_element('delete_button', ['%position%' => $nth])->click();
        $this->wait_for_form_update();
    }
    public function is_generation_possible(): bool
    {
        return !$this->get_element('generate_button')->has_attribute('disabled');
    }
    public function is_product_variant_removable(int $nth): bool
    {
        return $this->has_element('delete_button', ['%position%' => $nth]);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel_pricings' => '#sylius_admin_product_generate_variants_variants_%position% [data-test-channel-pricings]', 'code' => '#sylius_admin_product_generate_variants_variants_%position% [data-test-code]', 'delete_button' => '#sylius_admin_product_generate_variants_variants_%position% [data-test-delete-button]', 'form' => 'form', 'generate_button' => '[data-test-generate-button]', 'price' => '#sylius_admin_product_generate_variants_variants_%position%_channelPricings_%channel_code%_price']);
    }
}