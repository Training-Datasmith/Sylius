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

interface Discounted_Products_Checking_Period_Input_Element_Interface
{
    public function specify_period(int $period): void;
    public function get_period(): int;
}