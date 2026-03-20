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

use SM\Factory\Factory_Interface;
use SM\Sm_Exception;
use Sylius\Abstraction\State_Machine\Exception\State_Machine_Execution_Exception;
final readonly class Winzou_State_Machine_Adapter implements State_Machine_Interface
{
    public function __construct(private Factory_Interface $winzou_state_machine_factory)
    {
    }
    public function can(object $subject, string $graph_name, string $transition): bool
    {
        try {
            return $this->get_state_machine($subject, $graph_name)->can($transition);
        } catch (Sm_Exception $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
    }
    public function apply(object $subject, string $graph_name, string $transition, array $context = []): void
    {
        try {
            $this->get_state_machine($subject, $graph_name)->apply($transition);
        } catch (Sm_Exception $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
    }
    public function get_enabled_transitions(object $subject, string $graph_name): array
    {
        $state_machine = $this->get_state_machine($subject, $graph_name);
        return array_filter($this->get_all_transitions($state_machine), fn(Transition_Interface $transition): bool => $this->can($subject, $graph_name, $transition->get_name()));
    }
    /**
     * @return array<TransitionInterface>
     */
    private function get_all_transitions(\SM\State_Machine\State_Machine_Interface $state_machine): array
    {
        try {
            $transitions_config = $this->get_config($state_machine)['transitions'];
        } catch (\Reflection_Exception $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
        $transitions = [];
        foreach ($transitions_config as $transition_name => $transition_config) {
            $froms = $transition_config['from'];
            $tos = [$transition_config['to']];
            $transitions[] = new Transition($transition_name, $froms, $tos);
        }
        return $transitions;
    }
    /**
     * @throws \ReflectionException
     *
     * @return array{transitions: array<string, array{from: array<string>, to: string}>}
     */
    private function get_config(\SM\State_Machine\State_Machine_Interface $state_machine): array
    {
        $reflection = new \ReflectionClass($state_machine);
        $config_property = $reflection->get_property('config');
        return $config_property->get_value($state_machine);
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
    private function get_state_machine(object $subject, string $graph_name): \SM\State_Machine\State_Machine_Interface
    {
        try {
            return $this->winzou_state_machine_factory->get($subject, $graph_name);
        } catch (Sm_Exception $exception) {
            throw new State_Machine_Execution_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
    }
}