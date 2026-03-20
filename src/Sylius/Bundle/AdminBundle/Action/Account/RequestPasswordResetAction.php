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
namespace Sylius\Bundle\Admin_Bundle\Action\Account;

use Sylius\Bundle\Admin_Bundle\Form\Model\Password_Reset_Request;
use Sylius\Bundle\Admin_Bundle\Form\Request_Password_Reset_Type;
use Sylius\Bundle\Core_Bundle\Command\Admin\Account\Request_Reset_Password_Email;
use Sylius\Bundle\Core_Bundle\Provider\Flash_Bag_Provider;
use Symfony\Component\Form\Form_Factory_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Messenger\Message_Bus_Interface;
use Symfony\Component\Routing\Router_Interface;
use Twig\Environment;
final readonly class Request_Password_Reset_Action
{
    public function __construct(private Form_Factory_Interface $form_factory, private Message_Bus_Interface $message_bus, private Request_Stack $request_stack, private Router_Interface $router, private Environment $twig)
    {
    }
    public function __invoke(Request $request): Response
    {
        $form = $this->form_factory->create(Request_Password_Reset_Type::class);
        $form->handle_request($request);
        if ($form->is_submitted() && $form->is_valid()) {
            /** @var PasswordResetRequest $formData */
            $form_data = $form->get_data();
            $request_password_reset_message = new Request_Reset_Password_Email($form_data->get_email());
            $this->message_bus->dispatch($request_password_reset_message);
            Flash_Bag_Provider::get_flash_bag($this->request_stack)->add('success', 'sylius.admin.request_reset_password.success');
            $options = $request->attributes->get('_sylius', []);
            $redirect_route = $options['redirect'] ?? 'sylius_admin_login';
            if (is_array($redirect_route)) {
                return new Redirect_Response($this->router->generate($redirect_route['route'] ?? 'sylius_admin_login', $redirect_route['params'] ?? []));
            }
            return new Redirect_Response($this->router->generate($redirect_route));
        }
        return new Response($this->twig->render('@SyliusAdmin/security/request_password_reset.html.twig', ['form' => $form->create_view()]));
    }
}