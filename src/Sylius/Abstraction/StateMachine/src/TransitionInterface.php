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
namespace Sylius\Abstraction\State_Machine;

interface Transition_Interface
{
    public function get_name(): string;
    /**
     * @return array<string>|null
     */
    public function get_froms(): ?array;
    /**
     * @return array<string>|null
     */
    public function get_tos(): ?array;
}