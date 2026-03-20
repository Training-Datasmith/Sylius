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
namespace Sylius\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Behat\Hook\Before_Scenario;
use Psr\Cache\Cache_Item_Pool_Interface;
final readonly class Mailer_Context implements Context
{
    public function __construct(private Cache_Item_Pool_Interface $cache)
    {
    }
    #[Before_Scenario]
    public function purge_messages(): void
    {
        $this->cache->clear();
    }
}