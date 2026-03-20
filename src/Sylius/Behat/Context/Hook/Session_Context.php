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
namespace Sylius\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Behat\Hook\Before_Scenario;
use Symfony\Component\Http_Foundation\Exception\Session_Not_Found_Exception;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Session\Session_Factory_Interface;
use Symfony\Component\Http_Foundation\Session\Session_Interface;
final readonly class Session_Context implements Context
{
    public function __construct(private Request_Stack $request_stack, private ?Session_Factory_Interface $session_factory = null)
    {
    }
    #[Before_Scenario]
    public function start_session(): void
    {
        if (null === $this->session_factory) {
            return;
        }
        try {
            $this->request_stack->get_session();
        } catch (Session_Not_Found_Exception) {
            $session = $this->session_factory->create_session();
            $session->start();
            $session->save();
            $request = $this->request_stack->get_main_request();
            if (null !== $request) {
                $this->save_session_on_request($request, $session);
                return;
            }
            $this->save_session_on_new_request($session);
        }
    }
    private function save_session_on_new_request(Session_Interface $session): void
    {
        $request = new Request();
        $this->save_session_on_request($request, $session);
        $this->request_stack->push($request);
    }
    private function save_session_on_request(Request $request, Session_Interface $session): void
    {
        $request->set_session($session);
    }
}