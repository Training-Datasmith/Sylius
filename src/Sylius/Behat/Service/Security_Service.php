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

use Sylius\Behat\Service\Setter\Cookie_Setter_Interface;
use Sylius\Component\User\Model\User_Interface;
use Symfony\Component\Http_Foundation\Exception\Session_Not_Found_Exception;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Session\Session_Factory_Interface;
use Symfony\Component\Security\Core\Authentication\Token\Remember_Me_Token;
use Symfony\Component\Security\Core\Authentication\Token\Token_Interface;
use Symfony\Component\Security\Core\Authentication\Token\Username_Password_Token;
use Symfony\Component\Security\Core\Exception\Token_Not_Found_Exception;
final readonly class Security_Service implements Remember_Me_Aware_Security_Service_Interface
{
    private string $session_token_variable;
    public function __construct(private Request_Stack $request_stack, private Cookie_Setter_Interface $cookie_setter, private string $firewall_context_name, private ?Session_Factory_Interface $session_factory = null)
    {
        $this->session_token_variable = sprintf('_security_%s', $firewall_context_name);
    }
    public function log_in(User_Interface $user): void
    {
        $this->set_token(new Username_Password_Token($user, $this->firewall_context_name, $user->get_roles()));
    }
    public function log_in_with_remember_me(User_Interface $user): void
    {
        $token = new Remember_Me_Token($user, $this->firewall_context_name, 'secret');
        $this->set_token($token);
    }
    public function log_out(): void
    {
        try {
            $this->set_token_cookie();
        } catch (Session_Not_Found_Exception) {
        }
    }
    public function get_current_token(): Token_Interface
    {
        $serialized_token = $this->request_stack->get_session()->get($this->session_token_variable);
        if (null === $serialized_token) {
            throw new Token_Not_Found_Exception();
        }
        return unserialize($serialized_token);
    }
    public function restore_token(Token_Interface $token): void
    {
        $this->set_token($token);
    }
    private function set_token(Token_Interface $token): void
    {
        if (null !== $this->session_factory) {
            $session = $this->session_factory->create_session();
            $request = new Request();
            $request->set_session($session);
            $this->request_stack->push($request);
        }
        $this->set_token_cookie(serialize($token));
    }
    private function set_token_cookie($serialized_token = null): void
    {
        $session = $this->request_stack->get_session();
        $session->set($this->session_token_variable, $serialized_token);
        $session->save();
        $this->cookie_setter->set_cookie($session->get_name(), $session->get_id());
    }
}