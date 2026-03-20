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

use Symfony\Component\Http_Foundation\Response;
final class Sprintf_Response_Escaper
{
    public static function provide_message_with_escaped_response_content(string $message, Response $response): string
    {
        return sprintf('%s Received response: %s', $message, str_replace('%', '%%', json_encode(json_decode($response->get_content(), true), \JSON_PRETTY_PRINT)));
    }
}