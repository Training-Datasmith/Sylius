<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Component\Core\Checker;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final readonly class OrderPaymentMethodSelectionRequirementChecker implements OrderPaymentMethodSelectionRequirementCheckerInterface
{
    public function __construct(private PaymentMethodsResolverInterface $paymentMethodsResolver)
    {
    }

    /**
     * Determines whether the checkout must show a payment-method selection step.
     *
     * Returns false for zero-total orders (e.g. fully discounted). Returns true when the
     * channel does not allow skipping the payment step, or when the order has no payments
     * yet, or when any existing payment has more than one applicable payment method
     * available (so the customer must choose).
     *
     * @param OrderInterface $order The order being checked out
     *
     * @return bool True if the payment selection step must be presented to the customer
     *
     * @complexity O(n) where n is the number of payments on the order
     * @see PaymentMethodsResolverInterface::getSupportedMethods()
     */
    public function isPaymentMethodSelectionRequired(OrderInterface $order): bool
    {
        if ($order->getTotal() <= 0) {
            return false;
        }

        if (!$order->getChannel()->isSkippingPaymentStepAllowed() || $order->getPayments()->isEmpty()) {
            return true;
        }

        foreach ($order->getPayments() as $payment) {
            if (count($this->paymentMethodsResolver->getSupportedMethods($payment)) !== 1) {
                return true;
            }
        }

        return false;
    }
}
