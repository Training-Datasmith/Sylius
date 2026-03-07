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

namespace Sylius\Component\Core\Statistics\ValueObject;

class BusinessActivitySummary
{
    public function __construct(
        private readonly int $totalSales,
        private readonly int $paidOrdersCount,
        private readonly int $newCustomersCount,
    ) {
    }

    public function getTotalSales(): int
    {
        return $this->totalSales;
    }

    public function getPaidOrdersCount(): int
    {
        return $this->paidOrdersCount;
    }

    public function getNewCustomersCount(): int
    {
        return $this->newCustomersCount;
    }

    public function getAverageOrderValue(): int
    {
        if (0 === $this->paidOrdersCount) {
            return 0;
        }

        return (int) round($this->totalSales / $this->paidOrdersCount);
    }
}
