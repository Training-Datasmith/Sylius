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

use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Browser_Kit\Abstract_Browser;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
final class Api_Platform_Client implements Api_Client_Interface
{
    private ?Request_Interface $request = null;
    private ?Response $last_response = null;
    public function __construct(private readonly Abstract_Browser $client, private readonly Shared_Storage_Interface $shared_storage, private readonly Request_Factory_Interface $request_factory, private readonly Response_Checker_Interface $response_checker, private readonly string $authorization_header, private readonly string $section)
    {
    }
    public function index(string $resource, array $query_parameters = [], bool $forget_response = false): Response
    {
        $this->request = $this->request_factory->index($this->section, $resource, $this->authorization_header, $this->get_token(), $query_parameters);
        return $this->request($this->request, $forget_response);
    }
    public function show_by_iri(string $iri, bool $forget_response = false): Response
    {
        $request = $this->request_factory->custom($iri, Http_Request::METHOD_GET);
        $request->authorize($this->get_token(), $this->authorization_header);
        return $this->request($request, $forget_response);
    }
    /** @param array<string, string> $queryParameters */
    public function sub_resource_index(string $resource, string $sub_resource, string $id, array $query_parameters = [], bool $forget_response = false): Response
    {
        $this->request = $this->request_factory->sub_resource_index($this->section, $resource, $id, $sub_resource, $query_parameters);
        $this->request->authorize($this->get_token(), $this->authorization_header);
        return $this->request($this->request, $forget_response);
    }
    public function show(string $resource, string $id, bool $forget_response = false): Response
    {
        return $this->request($this->request_factory->show($this->section, $resource, $id, $this->authorization_header, $this->get_token()), $forget_response);
    }
    public function create(?Request_Interface $request = null, bool $forget_response = false): Response
    {
        return $this->request($request ?? $this->request, $forget_response);
    }
    public function update(bool $forget_response = false): Response
    {
        return $this->request($this->request, $forget_response);
    }
    public function resend(bool $forget_response = false): Response
    {
        return $this->request($this->request, $forget_response);
    }
    public function delete(string $resource, string $id, bool $forget_response = false): Response
    {
        return $this->request($this->request_factory->delete($this->section, $resource, $id, $this->authorization_header, $this->get_token()), $forget_response);
    }
    public function filter(): Response
    {
        return $this->request($this->request);
    }
    public function sort(array $sorting): Response
    {
        if ($this->request === null) {
            throw new \RuntimeException('There is no request to sort.');
        }
        $this->request->update_parameters(['order' => $sorting]);
        return $this->request($this->request);
    }
    /** @param array<string, mixed> $content */
    public function apply_transition(string $resource, string $id, string $transition, array $content = []): Response
    {
        $request = $this->request_factory->transition($this->section, $resource, $id, $transition);
        $request->authorize($this->get_token(), $this->authorization_header);
        $request->set_content($content);
        return $this->request($request);
    }
    public function custom_item_action(string $resource, string $id, string $type, string $action): Response
    {
        $request = $this->request_factory->custom_item_action($this->section, $resource, $id, $type, $action);
        $request->authorize($this->get_token(), $this->authorization_header);
        return $this->request($request);
    }
    public function custom_action(string $url, string $method): Response
    {
        $request = $this->request_factory->custom($url, $method);
        $request->authorize($this->get_token(), $this->authorization_header);
        return $this->request($request);
    }
    public function execute_custom_request(Request_Interface $request): Response
    {
        $request->authorize($this->get_token(), $this->authorization_header);
        return $this->request($request);
    }
    public function build_create_request(string $url): self
    {
        $this->validate_uri($url);
        $this->request = $this->request_factory->default(section: $this->section, url: $url, method: 'POST', token: $this->get_token());
        return $this;
    }
    public function build_update_request(string $uri, ?string $id = null): self
    {
        $this->validate_uri($uri);
        if ($id !== null) {
            $uri = sprintf('%s/%s', $uri, $id);
        }
        $response = $this->request_get($uri);
        $this->request = $this->request_factory->update($this->section, $uri, $this->authorization_header, $this->get_token());
        $this->request->set_content(json_decode($response->get_content(), true));
        return $this;
    }
    public function build_custom_update_request(string $uri, ?string $id = null): self
    {
        $this->request = $this->request_factory->update($this->section, $uri, $this->authorization_header, $this->get_token());
        return $this;
    }
    public function add_parameter(string $key, bool|int|string $value): self
    {
        $this->request->update_parameters([$key => $value]);
        return $this;
    }
    public function set_request_data(array $data): self
    {
        $this->request->set_content($data);
        return $this;
    }
    public function add_filter(string $key, bool|int|string $value): void
    {
        $this->add_parameter($key, $value);
    }
    public function clear_parameters(): void
    {
        $this->request->clear_parameters();
    }
    public function add_file(string $key, Uploaded_File $file): void
    {
        $this->request->update_files([$key => $file]);
    }
    /** @param array<string, mixed> $value */
    public function add_request_data(string $key, array|bool|int|string|null $value): self
    {
        $this->request->update_content([$key => $value]);
        return $this;
    }
    /** @param array<string, mixed> $value */
    public function replace_request_data(string $key, array|bool|int|string|null $value): void
    {
        $request_content = $this->request->get_content();
        $this->request->set_content(array_replace($request_content, [$key => $value]));
    }
    /** @param array<string, mixed> $data */
    public function update_request_data(array $data): void
    {
        $this->request->update_content($data);
    }
    /** @param array<string, mixed> $data */
    public function set_sub_resource_data(string $key, array $data): void
    {
        $this->request->set_sub_resource($key, $data);
    }
    /** @param array<string, mixed> $data */
    public function add_sub_resource_data(string $key, array $data): void
    {
        $this->request->add_sub_resource($key, $data);
    }
    public function remove_sub_resource_iri(string $sub_resource_key, string $iri): void
    {
        $this->request->remove_sub_resource($sub_resource_key, $iri);
    }
    public function remove_sub_resource_object(string $sub_resource_key, string $value, string $key = '@id'): void
    {
        $this->request->remove_sub_resource($sub_resource_key, $value, $key);
    }
    /** @return array<string, mixed> */
    public function get_content(): array
    {
        return $this->request->get_content();
    }
    public function get_last_response(): Response
    {
        if (null === $this->last_response) {
            throw new \RuntimeException('There is no last response.');
        }
        return $this->last_response;
    }
    public function get_token(): ?string
    {
        return $this->shared_storage->has('token') ? $this->shared_storage->get('token') : null;
    }
    public function request_get(string $uri, array $query_parameters = [], array $headers = []): Response
    {
        $this->validate_uri($uri);
        $this->request = $this->request_factory->default($this->section, $uri, Http_Request::METHOD_GET, $query_parameters, $headers)->authorize($this->get_token(), $this->authorization_header);
        return $this->request();
    }
    public function request_patch(string $uri, array $body = [], array $query_parameters = [], array $headers = []): Response
    {
        $this->validate_uri($uri);
        $this->request = $this->request_factory->default($this->section, $uri, Http_Request::METHOD_PATCH, $query_parameters, $headers)->authorize($this->get_token(), $this->authorization_header);
        $this->request->set_content($body);
        return $this->request();
    }
    public function request_delete(string $uri): Response
    {
        $this->request = $this->request_factory->default($this->section, $uri, Http_Request::METHOD_DELETE)->authorize($this->get_token(), $this->authorization_header);
        return $this->request();
    }
    public function request(?Request_Interface $request = null, bool $forget_response = false): Response
    {
        if ($request === null) {
            $request = $this->request;
        }
        $this->set_server_parameters();
        $this->client->request($request->method(), $request->url(), $request->parameters(), $request->files(), $request->headers(), $request->content());
        /** @var Response $response */
        $response = $this->client->get_response();
        if (!$response->is_successful() && $response->get_status_code() >= 400) {
            $this->response_checker->append_error($response);
        }
        if (false === $forget_response) {
            $this->last_response = $response;
        }
        return $response;
    }
    private function set_server_parameters(): void
    {
        if ($this->shared_storage->has('hostname')) {
            $this->client->set_server_parameter('HTTP_HOST', $this->shared_storage->get('hostname'));
        }
        if ($this->shared_storage->has('current_locale_code')) {
            $this->client->set_server_parameter('HTTP_ACCEPT_LANGUAGE', $this->shared_storage->get('current_locale_code'));
        }
    }
    private function validate_uri(string $uri): void
    {
        if (str_starts_with($uri, '/')) {
            throw new \InvalidArgumentException('URI should not start with a slash.');
        }
        if (str_starts_with($uri, 'http')) {
            throw new \InvalidArgumentException('URI should not start with "http".');
        }
        if (str_starts_with($uri, 'api')) {
            throw new \InvalidArgumentException('URI should not start with "api".');
        }
    }
}