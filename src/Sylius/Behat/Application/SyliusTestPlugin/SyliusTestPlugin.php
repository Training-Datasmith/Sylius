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
namespace Sylius\Behat\Application\Sylius_Test_Plugin;

use Sylius\Bundle\Core_Bundle\Application\Sylius_Plugin_Trait;
use Symfony\Component\Http_Kernel\Bundle\Bundle;
final class Sylius_Test_Plugin extends Bundle
{
    use Sylius_Plugin_Trait;
}