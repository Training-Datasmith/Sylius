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
namespace Sylius\Bundle\Admin_Bundle\Action;

use Sylius\Bundle\Core_Bundle\Command_Dispatcher\Resend_Order_Confirmation_Email_Dispatcher_Interface;
use Sylius\Bundle\Core_Bundle\Provider\Flash_Bag_Provider;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
use Symfony\Component\Routing\Router_Interface;
use Symfony\Component\Security\Csrf\Csrf_Token;
use Symfony\Component\Security\Csrf\Csrf_Token_Manager_Interface;
final readonly class Resend_Order_Confirmation_Email_Action
{
    public function __construct(private Order_Repository_Interface $order_repository, private Resend_Order_Confirmation_Email_Dispatcher_Interface $resend_order_confirmation_email_dispatcher, private Csrf_Token_Manager_Interface $csrf_token_manager, private Request_Stack $request_stack, private Router_Interface $router)
    {
    }
    public function __invoke(Request $request): Response
    {
        $order_id = $request->attributes->get('id', '');
        if (!$this->csrf_token_manager->is_token_valid(new Csrf_Token($order_id, (string) $request->query->get('_csrf_token', '')))) {
            throw new Http_Exception(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }
        /** @var OrderInterface|null $order */
        $order = $this->order_repository->find_order_by_id($order_id);
        if ($order === null) {
            throw new Not_Found_Http_Exception(sprintf('The order with id %s has not been found', $order_id));
        }
        $this->resend_order_confirmation_email_dispatcher->dispatch($order);
        Flash_Bag_Provider::get_flash_bag($this->request_stack)->add('success', 'sylius.email.order_confirmation_resent');
        return $this->redirect($request, $order);
    }
    private function redirect(Request $request, Order_Interface $order): Redirect_Response
    {
        $redirect = $request->attributes->get('_sylius', [])['redirect'] ?? null;
        if (null === $redirect || is_array($redirect)) {
            return new Redirect_Response($this->router->generate($redirect['route'] ?? 'sylius_admin_order_show', $redirect['params'] ?? ['id' => $order->get_id()]));
        }
        return new Redirect_Response($this->router->generate($redirect));
    }
}