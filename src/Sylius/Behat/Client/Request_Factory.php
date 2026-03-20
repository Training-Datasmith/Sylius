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

use Symfony\Component\Http_Foundation\Request as HttpRequest;
final readonly class Request_Factory implements Request_Factory_Interface
{
    private const LINKED_DATA_JSON_CONTENT_TYPE = 'application/ld+json';
    private const UPLOAD_FILE_CONTENT_TYPE = 'multipart/form-data';
    public function __construct(private Content_Type_Guide_Interface $content_type_guide, private string $api_url_prefix)
    {
    }
    public function index(?string $section, string $resource, string $authorization_header, ?string $token = null, array $query_parameters = []): Request_Interface
    {
        $builder = Request_Builder::create_get(sprintf('%s/%s/%s%s', $this->api_url_prefix, $section, $resource, $this->get_query_string($query_parameters)));
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        if ($token) {
            $builder->with_header('HTTP_' . $authorization_header, 'Bearer ' . $token);
        }
        return $builder->build();
    }
    public function sub_resource_index(string $section, string $resource, string $id, string $sub_resource, array $query_parameters = []): Request_Interface
    {
        $builder = Request_Builder::create_get(sprintf('%s/%s/%s/%s/%s%s', $this->api_url_prefix, $section, $resource, $id, $sub_resource, $this->get_query_string($query_parameters)));
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        return $builder->build();
    }
    public function show(string $section, string $resource, string $id, string $authorization_header, ?string $token = null): Request_Interface
    {
        $builder = Request_Builder::create_get(sprintf('%s/%s/%s/%s', $this->api_url_prefix, $section, $resource, $id));
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        if ($token) {
            $builder->with_header('HTTP_' . $authorization_header, 'Bearer ' . $token);
        }
        return $builder->build();
    }
    public function create(string $section, string $resource, string $authorization_header, ?string $token = null): Request_Interface
    {
        $builder = Request_Builder::create_post(sprintf('%s/%s/%s', $this->api_url_prefix, $section, $resource));
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        $builder->with_header('CONTENT_TYPE', self::LINKED_DATA_JSON_CONTENT_TYPE);
        if ($token) {
            $builder->with_header('HTTP_' . $authorization_header, 'Bearer ' . $token);
        }
        return $builder->build();
    }
    public function update(string $section, string $uri, string $authorization_header, ?string $token = null): Request_Interface
    {
        $builder = Request_Builder::create_put(sprintf('%s/%s/%s', $this->api_url_prefix, $section, $uri))->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE)->with_header('CONTENT_TYPE', $this->content_type_guide->guide(Http_Request::METHOD_PUT));
        if ($token) {
            $builder->with_header('HTTP_' . $authorization_header, 'Bearer ' . $token);
        }
        return $builder->build();
    }
    public function delete(string $section, string $resource, string $id, string $authorization_header, ?string $token = null): Request_Interface
    {
        $builder = Request_Builder::create_delete(sprintf('%s/%s/%s/%s', $this->api_url_prefix, $section, $resource, $id));
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        if ($token) {
            $builder->with_header('HTTP_' . $authorization_header, 'Bearer ' . $token);
        }
        return $builder->build();
    }
    public function transition(string $section, string $resource, string $id, string $transition): Request_Interface
    {
        return $this->custom_item_action($section, $resource, $id, Http_Request::METHOD_PATCH, $transition);
    }
    public function custom_item_action(string $section, string $resource, string $id, string $type, string $action): Request_Interface
    {
        $builder = Request_Builder::create(sprintf('%s/%s/%s/%s/%s', $this->api_url_prefix, $section, $resource, $id, $action), $type);
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        $builder->with_header('CONTENT_TYPE', $this->content_type_guide->guide($type));
        return $builder->build();
    }
    public function upload(string $section, string $resource, array $files, string $authorization_header, ?string $token = null): Request_Interface
    {
        $builder = Request_Builder::create(sprintf('%s/%s/%s', $this->api_url_prefix, $section, $resource), Http_Request::METHOD_POST);
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        $builder->with_header('CONTENT_TYPE', self::UPLOAD_FILE_CONTENT_TYPE);
        if ($token) {
            $builder->with_header('HTTP_' . $authorization_header, 'Bearer ' . $token);
        }
        foreach ($files as $name => $value) {
            $builder->with_file($name, $value);
        }
        return $builder->build();
    }
    public function custom(string $url, string $method, array $additional_headers = [], ?string $token = null): Request_Interface
    {
        $builder = Request_Builder::create($url, $method);
        $builder->with_header('HTTP_ACCEPT', self::LINKED_DATA_JSON_CONTENT_TYPE);
        $builder->with_header('CONTENT_TYPE', $this->content_type_guide->guide($method));
        if ($token) {
            $builder->with_header('HTTP_Authorization', 'Bearer ' . $token);
        }
        foreach ($additional_headers as $name => $value) {
            $builder->with_header($name, $value);
        }
        return $builder->build();
    }
    public function default(string $section, string $url, string $method, array $query_parameters = [], array $additional_headers = [], ?string $token = null): Request_Interface
    {
        return $this->custom(sprintf('%s/%s/%s', $this->api_url_prefix, $section, $url) . $this->get_query_string($query_parameters), $method, $additional_headers, $token);
    }
    private function get_query_string(array $query_parameters): string
    {
        return count($query_parameters) > 0 ? '?' . http_build_query($query_parameters) : '';
    }
}