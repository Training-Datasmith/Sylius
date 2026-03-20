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

use Sylius\Bundle\Core_Bundle\Provider\Flash_Bag_Provider;
use Sylius\Component\Core\Exception\Resource_Delete_Exception;
use Sylius\Resource\Resource_Actions;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Kernel\Event\Exception_Event;
use Symfony\Component\Routing\Generator\Url_Generator_Interface;
final readonly class Resource_Delete_Exception_Listener
{
    public function __construct(private Url_Generator_Interface $router, private Request_Stack $request_stack)
    {
    }
    public function on_resource_delete_exception(Exception_Event $event): void
    {
        $exception = $event->get_throwable();
        if (!$exception instanceof Resource_Delete_Exception) {
            return;
        }
        $event_request = $event->get_request();
        if ($event_request->attributes->has('_api_operation')) {
            return;
        }
        Flash_Bag_Provider::get_flash_bag($this->request_stack)->add('error', ['message' => 'sylius.resource.delete_error', 'parameters' => ['%resource%' => $exception->get_resource_name()]]);
        $request_attributes = $event_request->attributes;
        $original_route = $request_attributes->get('_route', '');
        $referrer = $event_request->headers->get('referer');
        if (null !== $referrer) {
            $event->set_response(new Redirect_Response($referrer));
            return;
        }
        $event->set_response($this->create_redirect_response($original_route, Resource_Actions::INDEX));
    }
    private function create_redirect_response(string $original_route, string $target_action): Redirect_Response
    {
        $redirect_route = str_replace(Resource_Actions::DELETE, $target_action, $original_route);
        return new Redirect_Response($this->router->generate($redirect_route));
    }
}