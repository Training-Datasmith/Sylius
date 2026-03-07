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

namespace Sylius\Bundle\PayumBundle\Request;

use Payum\Core\Request\BaseGetStatus;
use Sylius\Component\Payment\Model\PaymentInterface;

class GetStatus extends BaseGetStatus
{
    /** @var string */
    protected $status;

    public function markNew(): void
    {
        $this->status = PaymentInterface::STATE_NEW;
    }

    public function isNew(): bool
    {
        return $this->status === PaymentInterface::STATE_NEW;
    }

    public function markSuspended(): void
    {
        $this->status = PaymentInterface::STATE_PROCESSING;
    }

    public function isSuspended()
    {
        return $this->status === PaymentInterface::STATE_PROCESSING;
    }

    public function markExpired(): void
    {
        $this->status = PaymentInterface::STATE_FAILED;
    }

    public function isExpired()
    {
        return $this->status === PaymentInterface::STATE_FAILED;
    }

    public function markCanceled(): void
    {
        $this->status = PaymentInterface::STATE_CANCELLED;
    }

    public function isCanceled()
    {
        return $this->status === PaymentInterface::STATE_CANCELLED;
    }

    public function markPending(): void
    {
        $this->status = PaymentInterface::STATE_PROCESSING;
    }

    public function isPending()
    {
        return $this->status === PaymentInterface::STATE_PROCESSING;
    }

    public function markFailed(): void
    {
        $this->status = PaymentInterface::STATE_FAILED;
    }

    public function isFailed()
    {
        return $this->status === PaymentInterface::STATE_FAILED;
    }

    public function markUnknown(): void
    {
        $this->status = PaymentInterface::STATE_UNKNOWN;
    }

    public function isUnknown()
    {
        return $this->status === PaymentInterface::STATE_UNKNOWN;
    }

    public function markCaptured(): void
    {
        $this->status = PaymentInterface::STATE_COMPLETED;
    }

    public function isCaptured()
    {
        return $this->status === PaymentInterface::STATE_COMPLETED;
    }

    public function isAuthorized()
    {
        return $this->status === PaymentInterface::STATE_AUTHORIZED;
    }

    public function markAuthorized(): void
    {
        $this->status = PaymentInterface::STATE_AUTHORIZED;
    }

    public function isRefunded()
    {
        return $this->status === PaymentInterface::STATE_REFUNDED;
    }

    public function markRefunded(): void
    {
        $this->status = PaymentInterface::STATE_REFUNDED;
    }

    public function isPayedout()
    {
        return $this->status === PaymentInterface::STATE_REFUNDED;
    }

    public function markPayedout(): void
    {
        $this->status = PaymentInterface::STATE_REFUNDED;
    }
}
