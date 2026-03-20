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

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Element\Sylius_Element;
class Associations_Element extends Sylius_Element implements Associations_Element_Interface
{
    public function has_association(string $association_name): bool
    {
        return [] !== $this->get_associated_products($this->get_element('associations'), $association_name);
    }
    public function is_associated_with(string $association_name, string $product_name): bool
    {
        $associations = $this->get_element('associations');
        /** @var NodeElement $product */
        foreach ($this->get_associated_products($associations, $association_name) as $product) {
            if ($product->get_text() === $product_name) {
                return true;
            }
        }
        return false;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['associations' => '[data-test-associations]']);
    }
    protected function get_associated_products(Node_Element $associations, string $name): array
    {
        return $associations->find_all('css', sprintf("div:contains('%s') ul li", $name));
    }
}