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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
final readonly class Calendar_Context implements Context
{
    public function __construct(private string $date_file_path)
    {
    }
    #[Given('it is :dateTime now')]
    public function it_is_now(string $date_time): void
    {
        file_put_contents($this->date_file_path, $date_time);
    }
}