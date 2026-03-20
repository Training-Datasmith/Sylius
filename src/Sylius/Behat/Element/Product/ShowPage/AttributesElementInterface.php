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

interface Attributes_Element_Interface
{
    public function has_attribute_in_locale(string $attribute, string $locale_code, string $value): bool;
    public function has_non_translatable_attribute(string $attribute, float|string $value): bool;
}