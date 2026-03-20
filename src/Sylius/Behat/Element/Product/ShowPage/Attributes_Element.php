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
class Attributes_Element extends Sylius_Element implements Attributes_Element_Interface
{
    public function has_attribute_in_locale(string $attribute, string $locale_code, string $value): bool
    {
        $attribute_value = $this->get_element('attribute_with_locale', ['%locale_code%' => $locale_code]);
        return str_contains($attribute_value->get_text(), $value) && str_contains($attribute_value->get_text(), $attribute);
    }
    public function has_non_translatable_attribute(string $attribute, float|string $value): bool
    {
        $attribute_element = $this->get_element('non_translatable-attribute');
        $has_name = $attribute_element->has('css', sprintf('[data-test-non-translatable-attribute-name="%s"]', $attribute));
        $has_value = $attribute_element->has('css', sprintf('[data-test-non-translatable-attribute-value="%s"]', $value));
        return $has_name && $has_value;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['attribute_name' => '[data-test-attribute-name="%name%"]', 'attribute_value' => '[data-test-attribute-value]', 'attribute_with_locale' => '[data-test-attribute-with-locale="%locale_code%"]', 'attribute_without_locale' => '[data-test-attribute-without-locale]', 'non_translatable-attribute' => '[data-test-non-translatable-attribute]']);
    }
}