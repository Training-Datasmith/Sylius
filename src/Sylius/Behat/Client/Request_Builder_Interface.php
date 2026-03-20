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
interface Request_Builder_Interface
{
    public static function create_get(string $uri): self;
    public static function create_post(string $uri): self;
    public static function create_put(string $uri): self;
    public static function create_delete(string $uri): self;
    /** @param array<string, mixed> $content */
    public function with_content(array $content): self;
    public function with_header(string $key, string $value): self;
    public function with_file(string $key, Uploaded_File $file): self;
    /** @param array<string, mixed> $value */
    public function with_parameter(string $key, array|string $value): self;
    public function build(): Request_Interface;
}