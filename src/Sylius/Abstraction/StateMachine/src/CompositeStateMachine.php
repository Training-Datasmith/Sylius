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
use Traversable;
use Webmozart\Assert\Assert;
class Composite_State_Machine implements State_Machine_Interface
{
    /** @var array<StateMachineInterface> */
    private array $state_machine_adapters;
    /**
     * @param iterable<StateMachineInterface> $stateMachineAdapters
     * @param array<string, string> $graphsToAdaptersMapping
     */
    public function __construct(iterable $state_machine_adapters, private readonly string $default_adapter, private array $graphs_to_adapters_mapping)
    {
        Assert::not_empty($state_machine_adapters, 'At least one state machine adapter should be provided.');
        Assert::all_is_instance_of($state_machine_adapters, State_Machine_Interface::class, sprintf('All state machine adapters should implement the "%s" interface.', State_Machine_Interface::class));
        $this->state_machine_adapters = $state_machine_adapters instanceof Traversable ? iterator_to_array($state_machine_adapters) : $state_machine_adapters;
    }
    /** @throws StateMachineExecutionException */
    public function can(object $subject, string $graph_name, string $transition): bool
    {
        return $this->get_state_machine_adapter($graph_name)->can($subject, $graph_name, $transition);
    }
    /** @throws StateMachineExecutionException */
    public function apply(object $subject, string $graph_name, string $transition, array $context = []): void
    {
        $this->get_state_machine_adapter($graph_name)->apply($subject, $graph_name, $transition, $context);
    }
    /** @throws StateMachineExecutionException */
    public function get_enabled_transitions(object $subject, string $graph_name): array
    {
        return $this->get_state_machine_adapter($graph_name)->get_enabled_transitions($subject, $graph_name);
    }
    /** @throws StateMachineExecutionException */
    public function get_transition_from_state(object $subject, string $graph_name, string $from_state): ?string
    {
        return $this->get_state_machine_adapter($graph_name)->get_transition_from_state($subject, $graph_name, $from_state);
    }
    /** @throws StateMachineExecutionException */
    public function get_transition_to_state(object $subject, string $graph_name, string $to_state): ?string
    {
        return $this->get_state_machine_adapter($graph_name)->get_transition_to_state($subject, $graph_name, $to_state);
    }
    private function get_state_machine_adapter(string $graph_name): State_Machine_Interface
    {
        if (isset($this->graphs_to_adapters_mapping[$graph_name])) {
            return $this->state_machine_adapters[$this->graphs_to_adapters_mapping[$graph_name]];
        }
        return $this->state_machine_adapters[$this->default_adapter];
    }
}