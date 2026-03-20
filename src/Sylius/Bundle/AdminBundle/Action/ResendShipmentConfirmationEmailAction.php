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

use Sylius\Bundle\Core_Bundle\Command_Dispatcher\Resend_Shipment_Confirmation_Email_Dispatcher_Interface;
use Sylius\Bundle\Core_Bundle\Provider\Flash_Bag_Provider;
use Sylius\Component\Core\Model\Shipment_Interface;
use Sylius\Component\Core\Repository\Shipment_Repository_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
use Symfony\Component\Security\Csrf\Csrf_Token;
use Symfony\Component\Security\Csrf\Csrf_Token_Manager_Interface;
final readonly class Resend_Shipment_Confirmation_Email_Action
{
    public function __construct(private Shipment_Repository_Interface $shipment_repository, private Resend_Shipment_Confirmation_Email_Dispatcher_Interface $resend_shipment_confirmation_dispatcher, private Csrf_Token_Manager_Interface $csrf_token_manager, private Request_Stack $request_stack)
    {
    }
    public function __invoke(Request $request): Response
    {
        $shipment_id = $request->attributes->get('id', '');
        if (!$this->csrf_token_manager->is_token_valid(new Csrf_Token($shipment_id, (string) $request->query->get('_csrf_token', '')))) {
            throw new Http_Exception(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }
        /** @var ShipmentInterface|null $shipment */
        $shipment = $this->shipment_repository->find($shipment_id);
        if ($shipment === null) {
            throw new Not_Found_Http_Exception(sprintf('The shipment with id %s has not been found', $shipment_id));
        }
        $this->resend_shipment_confirmation_dispatcher->dispatch($shipment);
        Flash_Bag_Provider::get_flash_bag($this->request_stack)->add('success', 'sylius.email.shipment_confirmation_resent');
        return new Redirect_Response($request->headers->get('referer'));
    }
}