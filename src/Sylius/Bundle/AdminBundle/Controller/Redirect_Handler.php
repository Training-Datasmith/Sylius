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

use Sylius\Bundle\Grid_Bundle\Storage\Filter_Storage_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Redirect_Handler_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration;
use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Http_Foundation\Response;
final readonly class Redirect_Handler implements Redirect_Handler_Interface
{
    public function __construct(private Redirect_Handler_Interface $decorated_redirect_handler, private Filter_Storage_Interface $filter_storage)
    {
    }
    public function redirect_to_resource(Request_Configuration $configuration, Resource_Interface $resource): Response
    {
        return $this->decorated_redirect_handler->redirect_to_resource($configuration, $resource);
    }
    public function redirect_to_index(Request_Configuration $configuration, ?Resource_Interface $resource = null): Response
    {
        return $this->decorated_redirect_handler->redirect_to_route($configuration, (string) $configuration->get_redirect_route('index'), array_merge($configuration->get_redirect_parameters($resource), $this->filter_storage->all()));
    }
    /** @param array<string, mixed> $parameters */
    public function redirect_to_route(Request_Configuration $configuration, string $route, array $parameters = []): Response
    {
        return $this->decorated_redirect_handler->redirect_to_route($configuration, $route, $parameters);
    }
    public function redirect(Request_Configuration $configuration, string $url, int $status = 302): Response
    {
        return $this->decorated_redirect_handler->redirect($configuration, $url, $status);
    }
    public function redirect_to_referer(Request_Configuration $configuration): Response
    {
        return $this->decorated_redirect_handler->redirect_to_referer($configuration);
    }
}