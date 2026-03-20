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
 * Backed enum providing type-safe access to order checkout state values.
 *
 * Mirrors the string constants defined in {@see \Sylius\Component\Core\OrderCheckoutStates}
 * with the advantage of exhaustive matching, IDE completion, and static analysis support.
 *
 * The string values are identical to the legacy interface constants to ensure
 * full backward compatibility with Doctrine-persisted state values.
 *
 * @since 2.0
 */
enum OrderCheckoutState: string
{
    case Cart             = 'cart';
    case Addressed        = 'addressed';
    case ShippingSelected = 'shipping_selected';
    case ShippingSkipped  = 'shipping_skipped';
    case PaymentSelected  = 'payment_selected';
    case PaymentSkipped   = 'payment_skipped';
    case Completed        = 'completed';

    /**
     * Returns a human-readable label for display purposes.
     *
     * @return string Sentence-cased description of this state
     */
    public function label(): string
    {
        return match ($this) {
            self::Cart             => 'Cart',
            self::Addressed        => 'Address entered',
            self::ShippingSelected => 'Shipping selected',
            self::ShippingSkipped  => 'Shipping skipped',
            self::PaymentSelected  => 'Payment selected',
            self::PaymentSkipped   => 'Payment skipped',
            self::Completed        => 'Completed',
        };
    }

    /**
     * Returns whether this state represents a fully completed checkout.
     *
     * @return bool True only for the Completed state
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
