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
namespace Sylius\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Sylius\Component\Core\Repository\Payment_Repository_Interface;
use Sylius\Component\Payment\Model\Payment_Method_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Payments_Context implements Context
{
    public function __construct(private Payment_Repository_Interface $payment_repository)
    {
    }
    #[Then('/^there should be no ("[^"]+" payments) in the registry$/')]
    public function payment_should_not_exist_in_the_registry(Payment_Method_Interface $payment_method): void
    {
        $payments = $this->payment_repository->find_by(['method' => $payment_method]);
        Assert::same($payments, []);
    }
}