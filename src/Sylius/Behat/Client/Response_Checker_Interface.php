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

use Symfony\Component\Http_Foundation\Response;
interface Response_Checker_Interface
{
    public function is_violation_with_message_in_response(Response $response, string $message, ?string $property = null): bool;
    public function count_collection_items(Response $response): int;
    public function count_total_collection_items(Response $response): int;
    public function get_collection(Response $response): array;
    public function get_collection_items_with_value(Response $response, string $key, string $value): array;
    public function get_value(Response $response, string $key);
    public function get_translation_value(Response $response, string $key, ?string $locale_code): string;
    public function get_error(Response $response): ?string;
    public function is_accepted(Response $response): bool;
    public function is_creation_successful(Response $response): bool;
    public function is_update_successful(Response $response): bool;
    public function is_show_successful(Response $response): bool;
    public function is_deletion_successful(Response $response): bool;
    public function has_access_denied(Response $response): bool;
    public function has_collection(Response $response): bool;
    public function has_value(Response $response, string $key, bool|int|string|null $value, bool $is_case_sensitive = true): bool;
    public function has_value_in_collection(Response $response, string $key, int|string $value): bool;
    public function has_item_with_value(Response $response, string $key, int|string $value): bool;
    /** @param array<string, int|string> $expectedValues */
    public function has_values_in_any_subresource_object_collection(Response $response, string $sub_resource, array $expected_values): bool;
    /** @param array<string, int|string> $expectedValues */
    public function has_values_in_subresource_object(Response $response, string $sub_resource, array $expected_values): bool;
    public function has_value_in_subresource_object(Response $response, string $sub_resource, string $key, bool|int|string $expected_value): bool;
    public function has_item_on_position_with_value(Response $response, int $position, string $key, array|string $value): bool;
    public function has_item_with_translation(Response $response, string $locale, string $key, string $translation): bool;
    /**
     * @param array<array-key, array> $items
     */
    public function has_item_with_translation_in_collection(array $items, string $locale, string $key, string $translation): bool;
    public function has_key(Response $response, string $key): bool;
    public function has_translation(Response $response, string $locale, string $key, string $translation): bool;
    public function has_item_with_values(Response $response, array $parameters): bool;
    public function get_response_content(Response $response): array;
    public function has_violation_with_message(Response $response, string $message, ?string $property = null): bool;
    public function clean_errors(): void;
    /** @return array{
     *     step: string,
     *     type: string,
     *     error: string[]
     * }
     */
    public function get_debug_errors(): array;
}