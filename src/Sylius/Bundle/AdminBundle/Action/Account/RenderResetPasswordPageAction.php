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

use Sylius\Bundle\Admin_Bundle\Form\Type\Reset_Password_Type;
use Sylius\Bundle\Core_Bundle\Provider\Flash_Bag_Provider;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Model\User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Symfony\Component\Form\Form_Factory_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Routing\Router_Interface;
use Twig\Environment;
final readonly class Render_Reset_Password_Page_Action
{
    /**
     * @param UserRepositoryInterface<UserInterface> $userRepository
     */
    public function __construct(private User_Repository_Interface $user_repository, private Form_Factory_Interface $form_factory, private Request_Stack $request_stack, private Router_Interface $router, private Environment $twig, private string $token_ttl)
    {
    }
    public function __invoke(Request $request, string $token): Response
    {
        /** @var AdminUserInterface|null $admin */
        $admin = $this->user_repository->find_one_by(['passwordResetToken' => $token]);
        if (null === $admin) {
            return new Redirect_Response($this->router->generate('sylius_admin_login'));
        }
        $lifetime = new \DateInterval($this->token_ttl);
        if (!$admin->is_password_request_non_expired($lifetime)) {
            return $this->handle_expired_password_request($request);
        }
        $form = $this->form_factory->create(Reset_Password_Type::class);
        return new Response($this->twig->render('@SyliusAdmin/security/reset_password.html.twig', ['form' => $form->create_view()]));
    }
    private function handle_expired_password_request(Request $request): Redirect_Response
    {
        Flash_Bag_Provider::get_flash_bag($this->request_stack)->add('error', 'sylius.admin.password_reset.token_expired');
        $attributes = $request->attributes->get('_sylius', []);
        $redirect = $attributes['redirect'] ?? 'sylius_admin_login';
        if (is_array($redirect)) {
            return new Redirect_Response($this->router->generate($redirect['route'] ?? 'sylius_admin_login', $redirect['params'] ?? []));
        }
        return new Redirect_Response($this->router->generate($redirect));
    }
}