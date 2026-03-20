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

use Symfony\Component\Http_Foundation\File\Uploaded_File;
use Symfony\Component\Http_Foundation\Response;
interface Api_Client_Interface
{
    public function request(?Request_Interface $request = null, bool $forget_response = false): Response;
    /** @param array<string, mixed> $queryParameters */
    public function index(string $resource, array $query_parameters = [], bool $forget_response = false): Response;
    public function show_by_iri(string $iri, bool $forget_response = false): Response;
    /** @param array<string, string> $queryParameters */
    public function sub_resource_index(string $resource, string $sub_resource, string $id, array $query_parameters = [], bool $forget_response = false): Response;
    public function show(string $resource, string $id, bool $forget_response = false): Response;
    public function create(?Request_Interface $request = null, bool $forget_response = false): Response;
    public function update(bool $forget_response = false): Response;
    public function delete(string $resource, string $id, bool $forget_response = false): Response;
    public function filter(): Response;
    /** @param array<string, mixed> $sorting */
    public function sort(array $sorting): Response;
    /** @param array<string, mixed> $content */
    public function apply_transition(string $resource, string $id, string $transition, array $content = []): Response;
    public function custom_item_action(string $resource, string $id, string $type, string $action): Response;
    public function custom_action(string $url, string $method): Response;
    public function resend(): Response;
    public function execute_custom_request(Request_Interface $request): Response;
    public function build_create_request(string $url): self;
    /** @param ?string $id Deprecated, pass the id as a part of the uri */
    public function build_update_request(string $uri, ?string $id = null): self;
    public function build_custom_update_request(string $uri, ?string $id = null): self;
    /** @param array<string, mixed> $data */
    public function set_request_data(array $data): self;
    public function add_parameter(string $key, bool|int|string $value): self;
    public function add_filter(string $key, bool|int|string $value): void;
    public function clear_parameters(): void;
    public function add_file(string $key, Uploaded_File $file): void;
    /** @param array<string, mixed> $value */
    public function add_request_data(string $key, array|bool|int|string|null $value): self;
    /** @param array<string, mixed> $value */
    public function replace_request_data(string $key, array|bool|int|string|null $value): void;
    /** @param array<string, mixed> $data */
    public function set_sub_resource_data(string $key, array $data): void;
    /** @param array<string, mixed> $data */
    public function add_sub_resource_data(string $key, array $data): void;
    public function remove_sub_resource_iri(string $sub_resource_key, string $iri): void;
    public function remove_sub_resource_object(string $sub_resource_key, string $value, string $key = '@id'): void;
    /** @param array<string, mixed> $data */
    public function update_request_data(array $data): void;
    /** @return array<string, mixed> */
    public function get_content(): array;
    public function get_last_response(): Response;
    public function get_token(): ?string;
    /**
     * @param array<string, int|string|bool> $queryParameters
     * @param array<string, string> $headers
     */
    public function request_get(string $uri, array $query_parameters = [], array $headers = []): Response;
    /**
     * @param array<string, int|string|bool> $body
     * @param array<string, int|string|bool> $queryParameters
     * @param array<string, string> $headers
     */
    public function request_patch(string $uri, array $body = [], array $query_parameters = [], array $headers = []): Response;
    public function request_delete(string $uri): Response;
}