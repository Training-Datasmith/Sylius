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

use Sylius\Bundle\Admin_Bundle\Form\Request_Password_Reset_Type;
use Symfony\Component\Form\Form_Factory_Interface;
use Symfony\Component\Http_Foundation\Response;
use Twig\Environment;
final readonly class Render_Request_Password_Reset_Page_Action
{
    public function __construct(private Environment $twig, private Form_Factory_Interface $form_factory)
    {
    }
    public function __invoke(): Response
    {
        $form = $this->form_factory->create(Request_Password_Reset_Type::class);
        return new Response($this->twig->render('@SyliusAdmin/security/request_password_reset.html.twig', ['form' => $form->create_view()]));
    }
}