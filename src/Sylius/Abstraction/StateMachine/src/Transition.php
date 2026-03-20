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

final readonly class Transition implements Transition_Interface
{
    /**
     * @param array<string>|null $froms
     * @param array<string>|null $tos
     */
    public function __construct(private string $name, private ?array $froms, private ?array $tos)
    {
    }
    public function get_name(): string
    {
        return $this->name;
    }
    public function get_froms(): ?array
    {
        return $this->froms;
    }
    public function get_tos(): ?array
    {
        return $this->tos;
    }
}