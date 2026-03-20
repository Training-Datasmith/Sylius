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

use Lexik\Bundle\Jwt_Authentication_Bundle\Response\Jwt_Authentication_Failure_Response;
use Sylius\Behat\Service\Sprintf_Response_Escaper;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Response_Checker implements Response_Checker_Interface
{
    /** @var array<array-key, string> */
    private array $errors;
    public function __construct()
    {
        $this->errors = [];
    }
    public function count_collection_items(Response $response): int
    {
        return count($this->get_collection($response));
    }
    public function count_total_collection_items(Response $response): int
    {
        return (int) $this->get_response_content_value($response, 'hydra:totalItems');
    }
    public function get_collection(Response $response): array
    {
        return $this->get_response_content_value($response, 'hydra:member');
    }
    public function get_collection_items_with_value(Response $response, string $key, string $value): array
    {
        return array_filter($this->get_collection($response), fn(array $item): bool => $item[$key] === $value);
    }
    public function get_value(Response $response, string $key)
    {
        return $this->get_response_content_value($response, $key);
    }
    public function get_translation_value(Response $response, string $key, ?string $locale_code = 'en_US'): string
    {
        $translations = $this->get_response_content_value($response, 'translations');
        return $translations[$locale_code][$key];
    }
    public function get_error(Response $response): ?string
    {
        if ($this->has_key($response, 'message')) {
            return $this->get_value($response, 'message');
        }
        if ($this->has_key($response, 'hydra:description')) {
            return $this->get_response_content_value($response, 'hydra:description');
        }
        return $response->get_content();
    }
    public function is_accepted(Response $response): bool
    {
        return $response->get_status_code() === Response::HTTP_ACCEPTED;
    }
    public function is_creation_successful(Response $response): bool
    {
        return $response->get_status_code() === Response::HTTP_CREATED;
    }
    public function is_deletion_successful(Response $response): bool
    {
        return $response->get_status_code() === Response::HTTP_NO_CONTENT;
    }
    public function has_access_denied(Response $response): bool
    {
        if (!$response instanceof Jwt_Authentication_Failure_Response) {
            return false;
        }
        return $response->get_message() === 'JWT Token not found' && $response->get_status_code() === Response::HTTP_UNAUTHORIZED;
    }
    public function has_collection(Response $response): bool
    {
        return $this->has_key($response, 'hydra:member');
    }
    public function is_show_successful(Response $response): bool
    {
        return $response->get_status_code() === Response::HTTP_OK;
    }
    public function is_update_successful(Response $response): bool
    {
        return $response->get_status_code() === Response::HTTP_OK;
    }
    public function has_value(Response $response, string $key, bool|int|string|null $value, bool $is_case_sensitive = true): bool
    {
        if ($is_case_sensitive) {
            return $this->get_response_content_value($response, $key) === $value;
        }
        return strcasecmp((string) $this->get_response_content_value($response, $key), (string) $value) === 0;
    }
    public function has_value_in_collection(Response $response, string $key, bool|int|string $value): bool
    {
        return in_array($value, $this->get_response_content_value($response, $key), true);
    }
    /** @param string|int $value */
    public function has_item_with_value(Response $response, string $key, $value): bool
    {
        foreach ($this->get_collection($response) as $resource) {
            if ($resource[$key] === $value) {
                return true;
            }
        }
        return false;
    }
    public function has_values_in_any_subresource_object_collection(Response $response, string $sub_resource, array $expected_values): bool
    {
        $resource_collection = $this->get_response_content_value($response, $sub_resource);
        $this->assert_is_array($resource_collection);
        foreach ($resource_collection as $resource) {
            $this->assert_is_array($resource);
            foreach ($expected_values as $key => $expected_value) {
                if (!array_key_exists($key, $resource)) {
                    continue 2;
                }
                if ($resource[$key] !== $expected_value) {
                    continue 2;
                }
            }
            return true;
        }
        return false;
    }
    public function has_values_in_subresource_object(Response $response, string $sub_resource, array $expected_values): bool
    {
        $resource = $this->get_response_content_value($response, $sub_resource);
        $this->assert_is_array($resource);
        $this->assert_all_expected_keys_are_present($expected_values, $resource);
        foreach ($expected_values as $key => $expected_value) {
            if ($resource[$key] !== $expected_value) {
                return false;
            }
        }
        return true;
    }
    public function has_value_in_subresource_object(Response $response, string $sub_resource, string $key, bool|int|string $expected_value): bool
    {
        $resource = $this->get_response_content_value($response, $sub_resource);
        $this->assert_is_array($resource);
        return $resource[$key] === $expected_value;
    }
    /** @param string|array $value */
    public function has_item_on_position_with_value(Response $response, int $position, string $key, $value): bool
    {
        return $this->get_collection($response)[$position][$key] === $value;
    }
    public function has_item_with_translation(Response $response, string $locale, string $key, string $translation): bool
    {
        if (!$this->has_collection($response)) {
            $resource = $this->get_response_content($response);
            if (isset($resource['translations'][$locale]) && $resource['translations'][$locale][$key] === $translation) {
                return true;
            }
        }
        foreach ($this->get_collection($response) as $resource) {
            if (isset($resource['translations'][$locale]) && $resource['translations'][$locale][$key] === $translation) {
                return true;
            }
        }
        return false;
    }
    public function has_item_with_translation_in_collection(array $items, string $locale, string $key, string $translation): bool
    {
        foreach ($items as $item) {
            if (isset($item['translations'][$locale]) && $item['translations'][$locale][$key] === $translation) {
                return true;
            }
        }
        return false;
    }
    public function has_key(Response $response, string $key): bool
    {
        $content = json_decode($response->get_content(), true);
        return array_key_exists($key, $content);
    }
    public function has_translation(Response $response, string $locale, string $key, string $translation): bool
    {
        $resource = $this->get_response_content($response);
        return isset($resource['translations'][$locale]) && $resource['translations'][$locale][$key] === $translation;
    }
    public function has_item_with_values(Response $response, array $parameters): bool
    {
        foreach ($this->get_collection($response) as $item) {
            if ($this->item_has_values($item, $parameters)) {
                return true;
            }
        }
        return false;
    }
    public function get_response_content(Response $response): array
    {
        return json_decode($response->get_content(), true);
    }
    public function has_violation_with_message(Response $response, string $message, ?string $property = null): bool
    {
        if (!$this->has_key($response, 'violations')) {
            return false;
        }
        $violations = $this->get_response_content($response)['violations'];
        foreach ($violations as $violation) {
            if ($violation['message'] === $message && $property === null) {
                return true;
            }
            if ($violation['message'] === $message && $property !== null && $violation['propertyPath'] === $property) {
                return true;
            }
        }
        return false;
    }
    public function is_violation_with_message_in_response(Response $response, string $message, ?string $property = null): bool
    {
        $violations = $this->get_response_content($response)['violations'] ?? null;
        if ($violations === null) {
            throw new \InvalidArgumentException('Response expected to have violations, but it does not.');
        }
        foreach ($violations as $violation) {
            if ($violation['message'] === $message && $property === null) {
                return true;
            }
            if ($violation['message'] === $message && $property !== null && $violation['propertyPath'] === $property) {
                return true;
            }
        }
        return false;
    }
    public function append_error(Response $response): Response_Checker_Interface
    {
        $this->errors[] = $this->get_error($response);
        return $this;
    }
    public function clean_errors(): void
    {
        $this->errors = [];
    }
    public function get_debug_errors(): array
    {
        return $this->errors;
    }
    private function get_response_content_value(Response $response, string $key)
    {
        $content = json_decode($response->get_content(), true);
        Assert::is_array($content, Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Content could not be parsed to array.', $response));
        Assert::key_exists($content, $key, sprintf('Expected to get: "%s" key in response, got keys: [%s]', $key, implode(', ', array_keys($content))));
        return $content[$key];
    }
    private function item_has_values(array $element, array $parameters): bool
    {
        foreach ($parameters as $key => $value) {
            if ($element[$key] !== $value) {
                return false;
            }
        }
        return true;
    }
    private function assert_is_array(mixed $resource): void
    {
        Assert::is_array($resource, sprintf('Expected to get an array, got "%s"', gettype($resource)));
    }
    /**
     * @param array<string, int|string> $expectedValues
     * @param array<string, int|string> $resource
     */
    private function assert_all_expected_keys_are_present(array $expected_values, array $resource): void
    {
        Assert::count(array_diff_key($expected_values, $resource), 0, sprintf('Expected values array has keys: [%s], that are not present in the responses keys: [%s]', implode(', ', array_keys(array_diff_key($expected_values, $resource))), implode(', ', array_keys($resource))));
    }
}