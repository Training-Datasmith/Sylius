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
namespace Sylius\Behat\Service;

class Response_Loader implements Response_Loader_Interface
{
    public function get_mocked_response($source): array
    {
        $source = $this->get_mocked_responses_folder() . '/' . $source;
        return (array) json_decode($this->get_file_contents($source));
    }
    public function get_expected_response($source): array
    {
        $source = $this->get_expected_responses_folder() . '/' . $source;
        return (array) json_decode($this->get_file_contents($source));
    }
    private function get_responses_folder(): string
    {
        return $this->get_called_class_folder() . '/Responses';
    }
    private function get_mocked_responses_folder(): string
    {
        return $this->get_responses_folder() . '/Mocked';
    }
    private function get_expected_responses_folder(): string
    {
        return $this->get_responses_folder() . '/Expected';
    }
    private function get_called_class_folder(): string
    {
        $called_class = static::class;
        return \dirname((new \ReflectionClass($called_class))->get_file_name());
    }
    /**
     * @param string $source
     *
     * @throws \RuntimeException
     */
    private function assert_source_exists($source): void
    {
        if (!file_exists($source)) {
            throw new \RuntimeException(sprintf('File %s does not exist', $source));
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function assert_content_is_not_empty(string $source, string|bool $content): void
    {
        if ('' === $content) {
            throw new \RuntimeException(sprintf('Something went wrong, file %s is empty', $source));
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function assert_content_is_proper_loaded(string $source, string|bool $content): void
    {
        if (false === $content) {
            throw new \RuntimeException(sprintf('Something went wrong, cannot open %s', $source));
        }
    }
    /**
     * @param string $source
     *
     * @throws \RuntimeException
     */
    private function assert_source_is_not_folder($source): void
    {
        if (true === is_dir($source)) {
            throw new \RuntimeException(sprintf('Given source %s is a folder!', $source));
        }
    }
    /**
     *
     * @return string
     * @throws \RuntimeException
     */
    private function get_file_contents(string $source): string|false
    {
        $this->assert_source_exists($source);
        $this->assert_source_is_not_folder($source);
        $content = file_get_contents($source, true);
        $this->assert_content_is_proper_loaded($source, $content);
        $this->assert_content_is_not_empty($source, $content);
        return $content;
    }
}