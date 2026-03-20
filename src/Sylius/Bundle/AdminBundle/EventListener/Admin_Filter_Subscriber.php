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

use Sylius\Bundle\Grid_Bundle\Storage\Filter_Storage_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Kernel\Event\Request_Event;
use Symfony\Component\Http_Kernel\Kernel_Events;
final readonly class Admin_Filter_Subscriber implements Event_Subscriber_Interface
{
    public function __construct(private Filter_Storage_Interface $filter_storage)
    {
    }
    public static function get_subscribed_events(): array
    {
        return [Kernel_Events::REQUEST => 'onKernelRequest'];
    }
    public function on_kernel_request(Request_Event $event): void
    {
        if (!$event->is_main_request()) {
            return;
        }
        $event_request = $event->get_request();
        if ('html' !== $event_request->get_request_format()) {
            return;
        }
        $request_attributes = $event_request->attributes;
        if (null === $request_attributes->get('_controller') || !$this->is_index_resource_route($request_attributes->get('_route', '')) || !$this->is_admin_section($request_attributes->get('_sylius', []))) {
            return;
        }
        if ($this->filter_storage->all() !== $event_request->query->all()) {
            $this->filter_storage->set($event_request->query->all());
        }
    }
    private function is_index_resource_route(string $route): bool
    {
        return str_ends_with($route, 'index');
    }
    /** @param array<string, mixed> $syliusParameters */
    private function is_admin_section(array $sylius_parameters): bool
    {
        return isset($sylius_parameters['section']) && 'admin' === $sylius_parameters['section'];
    }
}