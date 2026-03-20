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
use Behat\Hook\Before_Scenario;
use Sylius\Bundle\Theme_Bundle\Configuration\Test\Test_Theme_Configuration_Manager_Interface;
final readonly class Test_Theme_Context implements Context
{
    public function __construct(private Test_Theme_Configuration_Manager_Interface $test_theme_configuration_manager)
    {
    }
    #[Before_Scenario]
    public function purge_test_themes(): void
    {
        $this->test_theme_configuration_manager->clear();
    }
}