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
namespace Sylius\Bundle\Admin_Bundle\Controller;

use Sylius\Bundle\Core_Bundle\Security\User_Impersonator_Interface;
use Sylius\Bundle\User_Bundle\Provider\User_Provider_Interface;
use Sylius\Component\User\Model\User_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Foundation\Session\Session;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
use Symfony\Component\Routing\Router_Interface;
use Symfony\Component\Security\Core\Authorization\Authorization_Checker_Interface;
use Webmozart\Assert\Assert;
final readonly class Impersonate_User_Controller
{
    public function __construct(private User_Impersonator_Interface $impersonator, private Authorization_Checker_Interface $authorization_checker, private User_Provider_Interface $user_provider, private Router_Interface $router, private string $authorization_role)
    {
    }
    public function impersonate_action(Request $request, string $username): Response
    {
        if (!$this->authorization_checker->is_granted($this->authorization_role)) {
            throw new Http_Exception(Response::HTTP_UNAUTHORIZED);
        }
        $user = $this->user_provider->load_user_by_username($username);
        Assert::is_instance_of($user, User_Interface::class);
        $this->impersonator->impersonate($user);
        $this->add_flash($request, $username);
        $fallback_url = $this->router->generate('sylius_admin_customer_show', ['id' => $user->get_id()]);
        $referer = $request->headers->get('referer', $fallback_url);
        $redirect_url = $this->is_same_host($request, (string) $referer) ? $referer : $fallback_url;
        return new Redirect_Response($redirect_url);
    }
    private function is_same_host(Request $request, string $url): bool
    {
        $referer_host = parse_url($url, PHP_URL_HOST);
        return $referer_host === $request->get_host();
    }
    private function add_flash(Request $request, string $username): void
    {
        /** @var Session $session */
        $session = $request->get_session();
        $session->get_flash_bag()->add('success', ['message' => 'sylius.customer.impersonate', 'parameters' => ['%name%' => $username]]);
    }
}