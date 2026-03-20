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

use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Processor\Catalog_Promotion_Removal_Processor_Interface;
use Sylius\Component\Promotion\Exception\Catalog_Promotion_Not_Found_Exception;
use Sylius\Component\Promotion\Exception\Invalid_Catalog_Promotion_State_Exception;
use Symfony\Component\Http_Foundation\Exception\Bad_Request_Exception;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Foundation\Session\Session;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
final readonly class Remove_Catalog_Promotion_Action
{
    public function __construct(private Catalog_Promotion_Removal_Processor_Interface $catalog_promotion_removal_processor)
    {
    }
    public function __invoke(Request $request): Response
    {
        $catalog_promotion_code = $request->attributes->get('code');
        if (null === $catalog_promotion_code) {
            throw new Not_Found_Http_Exception('The catalog promotion has not been found');
        }
        try {
            $this->catalog_promotion_removal_processor->remove_catalog_promotion($catalog_promotion_code);
            /** @var Session $session */
            $session = $request->get_session();
            $session->get_flash_bag()->add('success', 'sylius.catalog_promotion.remove');
            return new Redirect_Response($request->headers->get('referer'));
        } catch (Catalog_Promotion_Not_Found_Exception) {
            throw new Not_Found_Http_Exception('The catalog promotion has not been found');
        } catch (Invalid_Catalog_Promotion_State_Exception $exception) {
            throw new Bad_Request_Exception($exception->get_message());
        }
    }
}