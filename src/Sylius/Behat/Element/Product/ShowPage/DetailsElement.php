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
namespace Sylius\Behat\Element\Product\Show_Page;

use Sylius\Behat\Element\Sylius_Element;
class Details_Element extends Sylius_Element implements Details_Element_Interface
{
    public function get_product_code(): string
    {
        return $this->get_element('product_code')->get_text();
    }
    public function has_channel(string $channel_code): bool
    {
        if ($this->has_element('channel', ['%channel_code%' => $channel_code])) {
            return true;
        }
        return false;
    }
    public function count_channels(): int
    {
        if (!$this->has_element('channel')) {
            return 0;
        }
        $channels = $this->get_document()->find_all('css', ['data-test-channel']);
        return \count($channels);
    }
    public function get_product_current_stock(): int
    {
        return (int) $this->get_element('current_stock')->get_text();
    }
    public function get_product_tax_category(): string
    {
        return $this->get_element('tax_category')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel' => '[data-test-channel="%channel_code%"]', 'current_stock' => '[data-test-current-stock]', 'product_code' => '[data-test-product-code]', 'tax_category' => '[data-test-tax-category]']);
    }
}