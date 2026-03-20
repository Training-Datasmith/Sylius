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
class Translations_Element extends Sylius_Element implements Translations_Element_Interface
{
    public function get_description(): string
    {
        return $this->get_element('description')->get_text();
    }
    public function get_product_meta_keywords(): string
    {
        return $this->get_element('meta_keywords')->get_text();
    }
    public function get_short_description(): string
    {
        return $this->get_element('short_description')->get_text();
    }
    public function get_meta_description(): string
    {
        return $this->get_element('meta_description')->get_text();
    }
    public function get_slug(): string
    {
        return $this->get_element('slug')->get_text();
    }
    public function get_name(): string
    {
        return $this->get_element('name')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['description' => '#product-translations [data-test-description]', 'meta_description' => '#product-translations [data-test-meta-description]', 'meta_keywords' => '#product-translations [data-test-meta-keywords]', 'name' => '#product-translations [data-test-product-name]', 'short_description' => '#product-translations [data-test-short-description]', 'slug' => '#product-translations [data-test-slug]']);
    }
}