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
namespace Sylius\Behat\Page\Admin\Catalog_Promotion;

use Sylius\Behat\Page\Sylius_Page;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_admin_catalog_promotion_show';
    }
    public function get_name(): string
    {
        return $this->get_element('name')->get_text();
    }
    public function get_start_date(): string
    {
        return $this->get_element('start_date')->get_text();
    }
    public function get_end_date(): string
    {
        return $this->get_element('end_date')->get_text();
    }
    public function get_priority(): int
    {
        return (int) $this->get_element('priority')->get_text();
    }
    public function has_action_with_percentage_discount(string $amount): bool
    {
        $amounts_elements = $this->get_element('actions')->find_all('css', '[data-test-action] [data-test-amount]');
        foreach ($amounts_elements as $amount_element) {
            if ($amount_element->get_text() === $amount) {
                return true;
            }
        }
        return false;
    }
    public function has_action_with_fixed_discount(string $amount, Channel_Interface $channel): bool
    {
        $amounts_elements = $this->get_element('actions')->find_all('css', '[data-test-' . $channel->get_code() . '-amount]');
        foreach ($amounts_elements as $amount_element) {
            if ($amount_element->get_text() === $amount) {
                return true;
            }
        }
        return false;
    }
    public function has_scope_with_variant(Product_Variant_Interface $variant): bool
    {
        $variants_elements = $this->get_element('scopes')->find_all('css', '[data-test-variants] li');
        foreach ($variants_elements as $variant_element) {
            if ($variant_element->get_text() === $variant->get_code()) {
                return true;
            }
        }
        return false;
    }
    public function has_scope_with_product(Product_Interface $product): bool
    {
        $products_elements = $this->get_element('scopes')->find_all('css', '[data-test-products] li');
        foreach ($products_elements as $product_element) {
            if ($product_element->get_text() === $product->get_code()) {
                return true;
            }
        }
        return false;
    }
    public function is_exclusive(): bool
    {
        return null !== $this->get_element('exclusive')->find('css', 'svg.text-green');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['actions' => '[data-test-actions]', 'end_date' => '[data-test-end-date]', 'exclusive' => '[data-test-exclusive]', 'name' => '[data-test-name]', 'priority' => '[data-test-priority]', 'scopes' => '[data-test-scopes]', 'start_date' => '[data-test-start-date]']);
    }
}