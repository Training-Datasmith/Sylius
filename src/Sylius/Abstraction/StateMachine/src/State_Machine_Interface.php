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

use Sylius\Abstraction\State_Machine\Exception\State_Machine_Execution_Exception;
interface State_Machine_Interface
{
    /**
     * @throws StateMachineExecutionException
     */
    public function can(object $subject, string $graph_name, string $transition): bool;
    /**
     * @param array<string, mixed> $context
     *
     * @throws StateMachineExecutionException
     */
    public function apply(object $subject, string $graph_name, string $transition, array $context = []): void;
    /**
     * @throws StateMachineExecutionException
     *
     * @return array<TransitionInterface>
     */
    public function get_enabled_transitions(object $subject, string $graph_name): array;
    /**
     * @throws StateMachineExecutionException
     */
    public function get_transition_from_state(object $subject, string $graph_name, string $from_state): ?string;
    /**
     * @throws StateMachineExecutionException
     */
    public function get_transition_to_state(object $subject, string $graph_name, string $to_state): ?string;
}