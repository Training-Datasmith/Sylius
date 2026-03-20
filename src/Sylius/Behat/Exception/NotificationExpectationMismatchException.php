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
namespace Sylius\Behat\Exception;

use Sylius\Behat\Notification_Type;
final class Notification_Expectation_Mismatch_Exception extends \RuntimeException
{
    public function __construct(Notification_Type $expected_type, $expected_message, $code = 0, ?\Exception $previous = null)
    {
        $message = sprintf('Expected *%s* notification with a "%s" message was not found', $expected_type, $expected_message);
        parent::__construct($message, $code, $previous);
    }
}