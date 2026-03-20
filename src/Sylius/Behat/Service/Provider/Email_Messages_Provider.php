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
namespace Sylius\Behat\Service\Provider;

use Psr\Cache\Cache_Item_Pool_Interface;
use Sylius\Behat\Service\Message_Send_Cacher;
final readonly class Email_Messages_Provider implements Email_Messages_Provider_Interface
{
    public function __construct(private Cache_Item_Pool_Interface $cache)
    {
    }
    public function provide(): array
    {
        return $this->cache->has_item(Message_Send_Cacher::CACHE_KEY) ? $this->cache->get_item(Message_Send_Cacher::CACHE_KEY)->get() : [];
    }
}