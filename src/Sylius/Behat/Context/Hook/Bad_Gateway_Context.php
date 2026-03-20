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
use Behat\Behat\Hook\Scope\After_Step_Scope;
use Behat\Hook\After_Step;
use Behat\Testwork\Tester\Result\Test_Result;
final class Bad_Gateway_Context implements Context
{
    #[After_Step]
    public function check_for_bad_gateway_error(After_Step_Scope $scope): void
    {
        if ($scope->get_test_result()->get_result_code() !== Test_Result::FAILED) {
            return;
        }
        $exception = $scope->get_test_result()->get_exception();
        if ($exception && str_contains((string) $exception->get_message(), '502')) {
            fwrite(\STDERR, "Encountered Bad Gateway (502) error, aborting further tests.\n");
            exit(1);
        }
    }
    #[After_Step]
    public function delay_after_step(): void
    {
        $delay = (int) (getenv('BEHAT_STEP_DELAY') ?: 0);
        if ($delay > 0) {
            usleep($delay * 1000);
        }
    }
}