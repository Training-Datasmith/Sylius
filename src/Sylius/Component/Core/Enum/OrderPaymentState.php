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

namespace Sylius\Component\Core\Enum;

/**
 * Backed enum providing type-safe access to order payment state values.
 *
 * Mirrors the string constants defined in {@see \Sylius\Component\Core\OrderPaymentStates}
 * with the advantage of exhaustive matching, IDE completion, and static analysis support.
 *
 * The string values are identical to the legacy interface constants to ensure
 * full backward compatibility with Doctrine-persisted state values.
 *
 * @since 2.0
 */
enum OrderPaymentState: string
{
    case Cart                = 'cart';
    case AwaitingPayment     = 'awaiting_payment';
    case PartiallyAuthorized = 'partially_authorized';
    case Authorized          = 'authorized';
    case PartiallyPaid       = 'partially_paid';
    case Paid                = 'paid';
    case PartiallyRefunded   = 'partially_refunded';
    case Refunded            = 'refunded';
    case Cancelled           = 'cancelled';

    /**
     * Returns whether the order has been fully paid.
     *
     * @return bool True when payment state is Paid
     */
    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    /**
     * Returns whether the order payment has been fully or partially refunded.
     *
     * @return bool True for PartiallyRefunded or Refunded states
     */
    public function isRefunded(): bool
    {
        return $this === self::PartiallyRefunded || $this === self::Refunded;
    }

    /**
     * Returns a human-readable label for display purposes.
     *
     * @return string Sentence-cased description of this payment state
     */
    public function label(): string
    {
        return match ($this) {
            self::Cart                => 'Cart',
            self::AwaitingPayment     => 'Awaiting payment',
            self::PartiallyAuthorized => 'Partially authorised',
            self::Authorized          => 'Authorised',
            self::PartiallyPaid       => 'Partially paid',
            self::Paid                => 'Paid',
            self::PartiallyRefunded   => 'Partially refunded',
            self::Refunded            => 'Refunded',
            self::Cancelled           => 'Cancelled',
        };
    }
}
