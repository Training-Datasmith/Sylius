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
namespace Sylius\Behat\Context\Cli;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Component\Core\Order_Payment_States;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Symfony\Bundle\Framework_Bundle\Console\Application;
use Symfony\Component\Console\Tester\Command_Tester;
use Symfony\Component\Http_Kernel\Kernel_Interface;
use Webmozart\Assert\Assert;
final class Cancel_Unpaid_Orders_Context implements Context
{
    private const CANCEL_UNPAID_ORDERS_COMMAND = 'sylius:cancel-unpaid-orders';
    private readonly Application $application;
    private ?Command_Tester $command_tester = null;
    public function __construct(Kernel_Interface $kernel, private readonly Order_Repository_Interface $order_repository)
    {
        $this->application = new Application($kernel);
    }
    #[When('I run cancel unpaid orders command')]
    public function run_cancel_unpaid_orders_command(): void
    {
        $command = $this->application->find(self::CANCEL_UNPAID_ORDERS_COMMAND);
        $this->command_tester = new Command_Tester($command);
        $this->command_tester->execute(['command' => self::CANCEL_UNPAID_ORDERS_COMMAND]);
    }
    #[Then('only the order with number :orderNumber should be canceled')]
    public function only_order_with_number_should_be_canceled(string $order_number): void
    {
        $orders = $this->order_repository->find_by(['paymentState' => Order_Payment_States::STATE_CANCELLED]);
        Assert::count($orders, 1);
        Assert::same($orders[0]->get_number(), $order_number);
    }
    #[Then('I should be informed that unpaid orders have been canceled')]
    public function should_be_informed_that_unpaid_orders_have_been_canceled(): void
    {
        Assert::contains($this->command_tester->get_display(), 'Unpaid orders have been canceled');
    }
}