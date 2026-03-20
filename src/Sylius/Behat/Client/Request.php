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

final class Request implements Request_Interface
{
    public function __construct(private readonly string $url, private readonly string $method, private array $parameters = [], private array $headers = [], private array $content = [], private array $files = [])
    {
    }
    public function url(): string
    {
        return $this->url;
    }
    public function method(): string
    {
        return $this->method;
    }
    public function headers(): array
    {
        return $this->headers;
    }
    public function content(): string
    {
        return json_encode($this->content);
    }
    public function get_content(): array
    {
        return $this->content;
    }
    public function set_content(array $content): void
    {
        $this->content = $content;
    }
    public function update_content(array $new_values): void
    {
        $this->content = $this->merge_arrays_uniquely($this->content, $new_values);
    }
    public function parameters(): array
    {
        return $this->parameters;
    }
    public function update_parameters(array $new_parameters): void
    {
        $this->parameters = $this->merge_arrays_uniquely($this->parameters, $new_parameters);
    }
    public function clear_parameters(): void
    {
        $this->parameters = [];
    }
    public function files(): array
    {
        return $this->files;
    }
    public function update_files(array $new_files): void
    {
        $this->files = array_merge($this->files, $new_files);
    }
    public function set_subresource(string $key, array $sub_resource): void
    {
        $this->content[$key] = $sub_resource;
    }
    public function add_sub_resource(string $key, array $sub_resource): void
    {
        $this->content[$key][] = $sub_resource;
    }
    public function remove_sub_resource(string $sub_resource_key, string $value, string $key = '@id'): void
    {
        foreach ($this->content[$sub_resource_key] as $index => $object_or_iri) {
            if (is_array($object_or_iri)) {
                if (isset($object_or_iri[$key]) && $object_or_iri[$key] === $value) {
                    unset($this->content[$sub_resource_key][$index]);
                }
                continue;
            }
            if ($object_or_iri === $value) {
                unset($this->content[$sub_resource_key][$index]);
            }
        }
    }
    public function authorize(?string $token, string $authorization_header): self
    {
        if ($token !== null) {
            $this->headers['HTTP_' . $authorization_header] = 'Bearer ' . $token;
        }
        return $this;
    }
    private function merge_arrays_uniquely(array $first_array, array $second_array): array
    {
        foreach ($second_array as $key => $value) {
            if (is_string($key) && str_ends_with($key, '[]')) {
                $key = substr($key, 0, -2);
                $first_array[$key][] = $value;
                continue;
            }
            if (is_array($value) && is_array(@$first_array[$key])) {
                $value = $this->merge_arrays_uniquely($first_array[$key], $value);
            }
            $first_array[$key] = $value;
        }
        return $first_array;
    }
}