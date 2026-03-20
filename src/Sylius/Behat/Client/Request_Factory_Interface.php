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
namespace Sylius\Behat\Client;

interface Request_Factory_Interface
{
    public function index(?string $section, string $resource, string $authorization_header, ?string $token = null): Request_Interface;
    /**
     * @param array<string, mixed> $queryParameters
     */
    public function sub_resource_index(string $section, string $resource, string $id, string $sub_resource, array $query_parameters = []): Request_Interface;
    public function show(string $section, string $resource, string $id, string $authorization_header, ?string $token = null): Request_Interface;
    public function create(string $section, string $resource, string $authorization_header, ?string $token = null): Request_Interface;
    public function update(string $section, string $uri, string $authorization_header, ?string $token = null): Request_Interface;
    public function delete(string $section, string $resource, string $id, string $authorization_header, ?string $token = null): Request_Interface;
    public function upload(string $section, string $resource, array $files, string $authorization_header, ?string $token = null): Request_Interface;
    public function transition(string $section, string $resource, string $id, string $transition): Request_Interface;
    public function custom_item_action(string $section, string $resource, string $id, string $type, string $action): Request_Interface;
    public function custom(string $url, string $method, array $additional_headers = [], ?string $token = null): Request_Interface;
    public function default(string $section, string $url, string $method, array $query_parameters = [], array $additional_headers = [], ?string $token = null): Request_Interface;
}