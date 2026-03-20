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
namespace Sylius\Behat\Service\Setter;

use Sylius\Component\Channel\Model\Channel_Interface;
interface Channel_Context_Setter_Interface
{
    public function set_channel(Channel_Interface $channel);
}