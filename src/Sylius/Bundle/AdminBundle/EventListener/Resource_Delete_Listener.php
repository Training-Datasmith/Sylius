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

use Doctrine\DBAL\Exception\Foreign_Key_Constraint_Violation_Exception;
use Sylius\Component\Core\Exception\Resource_Delete_Exception;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Kernel\Event\Exception_Event;
final readonly class Resource_Delete_Listener
{
    public function on_resource_delete(Exception_Event $event): void
    {
        $exception = $event->get_throwable();
        if (!$exception instanceof Foreign_Key_Constraint_Violation_Exception) {
            return;
        }
        if (!$event->is_main_request() || 'html' !== $event->get_request()->get_request_format()) {
            return;
        }
        $event_request = $event->get_request();
        $request_attributes = $event_request->attributes;
        $original_route = $request_attributes->get('_route', '');
        if (!$this->is_method_delete($event_request) || !$this->is_sylius_route($original_route) || !$this->is_admin_section($request_attributes->get('_sylius', []))) {
            return;
        }
        $resource_name = $this->get_resource_name_from_route($original_route);
        if (null === $request_attributes->get('_controller')) {
            return;
        }
        throw new Resource_Delete_Exception($resource_name);
    }
    private function get_resource_name_from_route(string $route): string
    {
        $route = str_replace('_bulk', '', $route);
        $route_array = explode('_', $route);
        $route_array_without_action = array_slice($route_array, 0, count($route_array) - 1);
        $route_array_without_prefixes = array_slice($route_array_without_action, 2);
        return trim(implode(' ', $route_array_without_prefixes));
    }
    private function is_method_delete(Request $request): bool
    {
        return Request::METHOD_DELETE === $request->get_method();
    }
    private function is_sylius_route(string $route): bool
    {
        return str_starts_with($route, 'sylius');
    }
    /** @param array<string, mixed> $syliusParameters */
    private function is_admin_section(array $sylius_parameters): bool
    {
        return array_key_exists('section', $sylius_parameters) && 'admin' === $sylius_parameters['section'];
    }
}