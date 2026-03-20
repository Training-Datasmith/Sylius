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

use Sylius\Bundle\Admin_Bundle\Form\Model\Password_Reset;
use Sylius\Bundle\Admin_Bundle\Form\Type\Reset_Password_Type;
use Sylius\Bundle\Core_Bundle\Command_Dispatcher\Reset_Password_Dispatcher_Interface;
use Sylius\Bundle\Core_Bundle\Provider\Flash_Bag_Provider;
use Symfony\Component\Form\Form_Factory_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Routing\Router_Interface;
use Twig\Environment;
final readonly class Reset_Password_Action
{
    public function __construct(private Form_Factory_Interface $form_factory, private Reset_Password_Dispatcher_Interface $reset_password_dispatcher, private Request_Stack $request_stack, private Router_Interface $router, private Environment $twig)
    {
    }
    public function __invoke(Request $request, string $token): Response
    {
        $form = $this->form_factory->create(Reset_Password_Type::class);
        $form->handle_request($request);
        if ($form->is_submitted() && $form->is_valid()) {
            /** @var PasswordReset $passwordReset */
            $password_reset = $form->get_data();
            $this->reset_password_dispatcher->dispatch($token, $password_reset->get_password());
            Flash_Bag_Provider::get_flash_bag($this->request_stack)->add('success', 'sylius.admin.password_reset.success');
            $attributes = $request->attributes->get('_sylius', []);
            $redirect = $attributes['redirect'] ?? 'sylius_admin_login';
            if (is_array($redirect)) {
                return new Redirect_Response($redirect['route'] ?? 'sylius_admin_login', $redirect['params'] ?? []);
            }
            return new Redirect_Response($this->router->generate($redirect));
        }
        return new Response($this->twig->render('@SyliusAdmin/security/reset_password.html.twig', ['form' => $form->create_view()]));
    }
}