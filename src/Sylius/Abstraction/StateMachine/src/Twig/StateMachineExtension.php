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
namespace Sylius\Abstraction\State_Machine\Twig;

use Sylius\Abstraction\State_Machine\State_Machine_Interface;
use Twig\Extension\Abstract_Extension;
use Twig\Twig_Function;
final class State_Machine_Extension extends Abstract_Extension
{
    public function __construct(private readonly State_Machine_Interface $state_machine)
    {
    }
    public function get_functions(): array
    {
        return [new Twig_Function('sylius_sm_can', $this->state_machine->can(...)), new Twig_Function('sylius_sm_transitions', $this->state_machine->get_enabled_transitions(...))];
    }
}