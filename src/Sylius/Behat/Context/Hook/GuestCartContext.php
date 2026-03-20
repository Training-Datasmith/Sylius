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
namespace Sylius\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Behat\Hook\After_Scenario;
final readonly class Guest_Cart_Context implements Context
{
    public function __construct(private string $guest_cart_token_file_path)
    {
    }
    #[After_Scenario]
    public function delete_temporary_guest_token(): void
    {
        if (file_exists($this->guest_cart_token_file_path)) {
            unlink($this->guest_cart_token_file_path);
        }
    }
}