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

interface Request_Interface
{
    public function url(): string;
    public function method(): string;
    /** @return array<string, mixed> */
    public function headers(): array;
    public function content(): string;
    /** @return array<string, mixed> */
    public function get_content(): array;
    /** @param array<string, mixed> $content */
    public function set_content(array $content): void;
    /** @param array<string, mixed> $newValues */
    public function update_content(array $new_values): void;
    /** @return array<string, mixed> */
    public function parameters(): array;
    /** @param array<string, mixed> $newParameters */
    public function update_parameters(array $new_parameters): void;
    public function clear_parameters(): void;
    /** @return array<string, mixed> */
    public function files(): array;
    /** @param array<string, mixed> $newFiles */
    public function update_files(array $new_files): void;
    /** @param array<string, mixed> $subResource */
    public function set_sub_resource(string $key, array $sub_resource): void;
    /** @param array<string, mixed> $subResource */
    public function add_sub_resource(string $key, array $sub_resource): void;
    public function remove_sub_resource(string $sub_resource_key, string $value, string $key = '@id'): void;
    public function authorize(?string $token, string $authorization_header): self;
}