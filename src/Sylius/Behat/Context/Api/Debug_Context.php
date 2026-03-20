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
namespace Sylius\Behat\Context\Api;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\After_Scenario_Scope;
use Behat\Behat\Hook\Scope\After_Step_Scope;
use Behat\Hook\After_Scenario;
use Behat\Hook\After_Step;
use Sylius\Behat\Client\Response_Checker_Interface;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
use Symfony\Component\Console\Output\Console_Output;
final class Debug_Context implements Context
{
    /** @var array<int, array{type: DebugErrorType, error: string}> */
    private array $error_stack = [];
    public function __construct(private readonly Response_Checker_Interface $response_checker)
    {
    }
    #[After_Step]
    public function after_step(After_Step_Scope $scope): void
    {
        $debug_errors = $this->response_checker->get_debug_errors();
        if (empty($debug_errors)) {
            return;
        }
        $this->error_stack[] = ['step' => $scope->get_step()->get_text(), 'type' => Debug_Error_Type::API_RESPONSE, 'error' => $debug_errors];
        $this->response_checker->clean_errors();
    }
    #[After_Scenario]
    public function after_scenario(After_Scenario_Scope $scope): void
    {
        if (empty($this->error_stack)) {
            return;
        }
        if ($scope->get_test_result()->is_passed()) {
            $this->error_stack = [];
            return;
        }
        $output = new Console_Output();
        $style_key = new Output_Formatter_Style('cyan');
        $style_value = new Output_Formatter_Style('green');
        $output->get_formatter()->set_style('key', $style_key);
        $output->get_formatter()->set_style('value', $style_value);
        $json = json_encode($this->error_stack, \JSON_PRETTY_PRINT);
        $formatted_json = preg_replace('/"([^"]+)":/', '<key>"$1"</key>:', $json);
        $formatted_json = preg_replace('/: "([^"]+)"/', ': <value>"$1"</value>', (string) $formatted_json);
        $output->writeln($formatted_json);
        $this->error_stack = [];
    }
}