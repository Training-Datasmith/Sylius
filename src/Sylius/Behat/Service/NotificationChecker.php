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

use Sylius\Behat\Exception\Notification_Expectation_Mismatch_Exception;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Service\Accessor\Notification_Accessor_Interface;
use Webmozart\Assert\Assert;
final readonly class Notification_Checker implements Notification_Checker_Interface
{
    public function __construct(private Notification_Accessor_Interface $notification_accessor, private array $type_class_map)
    {
    }
    public function check_notification(string $message, Notification_Type $type): void
    {
        foreach ($this->notification_accessor->get_message_elements() as $message_element) {
            if (str_contains((string) $message_element->get_text(), $message) && $message_element->has_class($this->resolve_class($type))) {
                return;
            }
        }
        throw new Notification_Expectation_Mismatch_Exception($type, $message);
    }
    private function resolve_class(Notification_Type $type): string
    {
        Assert::key_exists($this->type_class_map, $type->__toString());
        return $this->type_class_map[$type->__toString()];
    }
}