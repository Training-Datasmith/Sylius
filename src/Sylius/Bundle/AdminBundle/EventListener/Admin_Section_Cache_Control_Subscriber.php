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
namespace Sylius\Bundle\Admin_Bundle\Event_Listener;

use Sylius\Bundle\Admin_Bundle\Section_Resolver\Admin_Section;
use Sylius\Bundle\Core_Bundle\Section_Resolver\Section_Provider_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Kernel\Event\Response_Event;
use Symfony\Component\Http_Kernel\Kernel_Events;
final readonly class Admin_Section_Cache_Control_Subscriber implements Event_Subscriber_Interface
{
    public function __construct(private Section_Provider_Interface $section_provider)
    {
    }
    public static function get_subscribed_events(): array
    {
        return [Kernel_Events::RESPONSE => 'setCacheControlDirectives'];
    }
    public function set_cache_control_directives(Response_Event $event): void
    {
        if (!$this->section_provider->get_section() instanceof Admin_Section) {
            return;
        }
        $response = $event->get_response();
        $response->headers->add_cache_control_directive('no-cache', true);
        $response->headers->add_cache_control_directive('max-age', '0');
        $response->headers->add_cache_control_directive('must-revalidate', true);
        $response->headers->add_cache_control_directive('no-store', true);
    }
}