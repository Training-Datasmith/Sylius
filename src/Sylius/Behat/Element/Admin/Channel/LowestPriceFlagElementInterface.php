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
namespace Sylius\Behat\Element\Admin\Channel;

interface Lowest_Price_Flag_Element_Interface
{
    public function enable(): void;
    public function disable(): void;
    public function is_enabled(): bool;
}