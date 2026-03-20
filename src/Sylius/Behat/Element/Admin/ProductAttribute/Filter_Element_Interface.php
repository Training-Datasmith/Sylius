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
namespace Sylius\Behat\Element\Admin\Product_Attribute;

interface Filter_Element_Interface
{
    public function choose_type(string $type): void;
    public function choose_translatable(string $translatable): void;
    public function filter(): void;
}