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
namespace Sylius\Abstraction\State_Machine;

use Symfony\Component\Http_Kernel\Bundle\Bundle;
class Sylius_State_Machine_Abstraction_Bundle extends Bundle
{
    public function get_path(): string
    {
        return dirname(__DIR__);
    }
}