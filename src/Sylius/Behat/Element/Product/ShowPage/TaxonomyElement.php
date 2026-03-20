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
class Taxonomy_Element extends Sylius_Element implements Taxonomy_Element_Interface
{
    public function get_product_main_taxon(): string
    {
        return $this->get_element('main_taxon')->get_text();
    }
    public function get_product_taxons(): string
    {
        return $this->get_element('product_taxons')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['main_taxon' => '[data-test-main-taxon]', 'product_taxons' => '[data-test-product-taxons]']);
    }
}