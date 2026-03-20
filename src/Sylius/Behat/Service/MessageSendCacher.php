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
namespace Sylius\Behat\Service;

use Psr\Cache\Cache_Item_Pool_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Mailer\Event\Message_Event;
final readonly class Message_Send_Cacher implements Event_Subscriber_Interface
{
    public const CACHE_KEY = 'messages';
    public function __construct(private Cache_Item_Pool_Interface $cache)
    {
    }
    public function on_message(Message_Event $event): void
    {
        if ($event->is_queued()) {
            return;
        }
        $item = $this->cache->get_item(self::CACHE_KEY);
        $messages = $item->is_hit() ? $item->get() : [];
        $messages[] = $event->get_message();
        $item->set($messages);
        $this->cache->save($item);
    }
    public static function get_subscribed_events(): array
    {
        return [Message_Event::class => ['onMessage', -1024]];
    }
}