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
class Content_Type_Guide implements Content_Type_Guide_Interface
{
    private const JSON_CONTENT_TYPE = 'application/json';
    private const PATCH_CONTENT_TYPE = 'application/merge-patch+json';
    private const LINKED_DATA_JSON_CONTENT_TYPE = 'application/ld+json';
    public function guide(string $method): string
    {
        if ($method === Http_Request::METHOD_PATCH) {
            return self::PATCH_CONTENT_TYPE;
        }
        if ($method === Http_Request::METHOD_PUT) {
            return self::LINKED_DATA_JSON_CONTENT_TYPE;
        }
        return self::JSON_CONTENT_TYPE;
    }
}