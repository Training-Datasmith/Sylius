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

interface Associations_Element_Interface
{
    public function has_association(string $association_name): bool;
    public function is_associated_with(string $association_name, string $product_name): bool;
}