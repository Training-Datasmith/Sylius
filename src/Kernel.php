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
namespace App;

use Symfony\Bundle\Framework_Bundle\Kernel\Micro_Kernel_Trait;
use Symfony\Component\Http_Kernel\Kernel as BaseKernel;
/** @final */
class Kernel extends Base_Kernel
{
    use Micro_Kernel_Trait;
}