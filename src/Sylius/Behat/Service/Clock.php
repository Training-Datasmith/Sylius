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

use Symfony\Component\Clock\Clock_Interface;
final class Clock implements Clock_Interface
{
    public function __construct(private string $date_file_path)
    {
    }
    public function sleep(float|int $seconds): void
    {
        // Intentionally left blank.
    }
    public function now(): \DateTimeImmutable
    {
        if (file_exists($this->date_file_path)) {
            $date_time = file_get_contents($this->date_file_path);
            return new \DateTimeImmutable($date_time);
        }
        return new \DateTimeImmutable();
    }
    public function with_time_zone(\DateTimeZone|string $timezone): static
    {
        $clone = clone $this;
        $clone->timezone = \is_string($timezone) ? new \DateTimeZone($timezone) : $timezone;
        return $clone;
    }
}