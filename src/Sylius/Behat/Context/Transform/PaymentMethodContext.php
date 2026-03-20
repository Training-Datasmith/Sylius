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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Payment\Repository\Payment_Method_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Payment_Method_Context implements Context
{
    public function __construct(private Payment_Method_Repository_Interface $payment_method_repository)
    {
    }
    #[Transform('/^"([^"]+)" payment(s)?$/')]
    #[Transform(':paymentMethod')]
    public function get_payment_method_by_name(string $payment_method_name)
    {
        $payment_methods = $this->payment_method_repository->find_by_name($payment_method_name, 'en_US');
        Assert::eq(count($payment_methods), 1, sprintf('%d payment methods has been found with name "%s".', count($payment_methods), $payment_method_name));
        return $payment_methods[0];
    }
}