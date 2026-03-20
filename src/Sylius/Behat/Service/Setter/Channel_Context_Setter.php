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
final readonly class Channel_Context_Setter implements Channel_Context_Setter_Interface
{
    public function __construct(private Cookie_Setter_Interface $cookie_setter)
    {
    }
    public function set_channel(Channel_Interface $channel): void
    {
        $this->cookie_setter->set_cookie('_channel_code', $channel->get_code());
    }
}