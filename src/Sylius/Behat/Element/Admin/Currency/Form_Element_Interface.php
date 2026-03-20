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
namespace Sylius\Behat\Element\Admin\Currency;

interface Form_Element_Interface
{
    public function choose_currency(string $currency_name): void;
    public function is_currency_available(string $currency_name): bool;
}