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
use Symfony\Component\Workflow\Exception\Exception_Interface as WorkflowExceptionInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition as SymfonyWorkflowTransition;
final readonly class Symfony_Workflow_Adapter implements State_Machine_Interface
{
    public function __construct(private Registry $symfony_workflow_registry)
    {
    }
    public function can(object $subject, string $graph_name, string $transition): bool
    {
        try {
            return $this->symfony_workflow_registry->get($subject, $graph_name)->can($subject, $transition);
        } catch (Workflow_Exception_Interface $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
    }
    public function apply(object $subject, string $graph_name, string $transition, array $context = []): void
    {
        try {
            $this->symfony_workflow_registry->get($subject, $graph_name)->apply($subject, $transition, $context);
        } catch (Workflow_Exception_Interface $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
    }
    public function get_enabled_transitions(object $subject, string $graph_name): array
    {
        try {
            $enabled_transitions = $this->symfony_workflow_registry->get($subject, $graph_name)->get_enabled_transitions($subject);
        } catch (Workflow_Exception_Interface $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
        return array_map(fn(Symfony_Workflow_Transition $transition): Transition_Interface => new Transition($transition->get_name(), $transition->get_froms(), $transition->get_tos()), $enabled_transitions);
    }
    public function get_transition_from_state(object $subject, string $graph_name, string $from_state): ?string
    {
        foreach ($this->get_enabled_transitions($subject, $graph_name) as $transition) {
            if ($transition->get_froms() !== null && in_array($from_state, $transition->get_froms(), true)) {
                return $transition->get_name();
            }
        }
        return null;
    }
    public function get_transition_to_state(object $subject, string $graph_name, string $to_state): ?string
    {
        foreach ($this->get_enabled_transitions($subject, $graph_name) as $transition) {
            if ($transition->get_tos() !== null && in_array($to_state, $transition->get_tos(), true)) {
                return $transition->get_name();
            }
        }
        return null;
    }
}