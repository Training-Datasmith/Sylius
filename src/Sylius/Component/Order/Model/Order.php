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

namespace Sylius\Component\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Resource\Model\TimestampableTrait;

class Order implements OrderInterface
{
    use TimestampableTrait;

    /** @var mixed */
    protected $id;

    /** @var \DateTimeInterface|null */
    protected $checkoutCompletedAt;

    /** @var string|null */
    protected $number;

    /** @var string|null */
    protected $notes;

    /** @var Collection<array-key, OrderItemInterface> */
    protected $items;

    /** @var int */
    protected $itemsTotal = 0;

    /** @var Collection<array-key, AdjustmentInterface> */
    protected $adjustments;

    /** @var int */
    protected $adjustmentsTotal = 0;

    /**
     * Items total + adjustments total.
     *
     * @var int
     */
    protected $total = 0;

    /** @var string */
    protected $state = OrderInterface::STATE_CART;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->adjustments = new ArrayCollection();

        $this->createdAt = new \DateTime();
    }

    /**
     * Returns the database identifier for this order.
     *
     * @return mixed Auto-generated integer or UUID depending on mapping configuration
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the timestamp at which checkout was completed, or null if still a cart.
     *
     * @return \DateTimeInterface|null Null when the order is still in cart/checkout state
     */
    public function getCheckoutCompletedAt(): ?\DateTimeInterface
    {
        return $this->checkoutCompletedAt;
    }

    /**
     * Sets the checkout completion timestamp.
     *
     * @param \DateTimeInterface|null $checkoutCompletedAt Null to revert the order to cart state
     */
    public function setCheckoutCompletedAt(?\DateTimeInterface $checkoutCompletedAt): void
    {
        $this->checkoutCompletedAt = $checkoutCompletedAt;
    }

    /**
     * Returns whether checkout has been completed for this order.
     *
     * @return bool True when checkoutCompletedAt has been set
     */
    public function isCheckoutCompleted(): bool
    {
        return null !== $this->checkoutCompletedAt;
    }

    /**
     * Marks checkout as complete by recording the current timestamp.
     *
     * @since 1.0
     */
    public function completeCheckout(): void
    {
        $this->checkoutCompletedAt = new \DateTime();
    }

    /**
     * Returns the human-readable order number (e.g. "000001234").
     *
     * @return string|null Null until the order number generator assigns one
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * Assigns the human-readable order number.
     *
     * @param string|null $number The generated order number, or null to clear
     */
    public function setNumber(?string $number): void
    {
        $this->number = $number;
    }

    /**
     * Returns the optional notes/instructions left by the customer.
     *
     * @return string|null Free-text customer notes, or null if none provided
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Sets customer notes for the order.
     *
     * @param string|null $notes Free-text customer notes; pass null to clear
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * Returns all order items in this order.
     *
     * @return Collection<array-key, OrderItemInterface>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Removes all items from this order and recalculates the items total.
     *
     * @complexity O(n) where n is the number of items
     */
    public function clearItems(): void
    {
        $this->items->clear();

        $this->recalculateItemsTotal();
    }

    /**
     * Returns the count of distinct order item lines (not total quantity).
     *
     * @return int Number of distinct order item entries
     */
    public function countItems(): int
    {
        return $this->items->count();
    }

    /**
     * Adds an order item and immediately updates the running items total.
     *
     * Idempotent — adding the same item twice has no effect.
     *
     * @param OrderItemInterface $item The item to add; its total is factored into itemsTotal
     */
    public function addItem(OrderItemInterface $item): void
    {
        if ($this->hasItem($item)) {
            return;
        }

        $this->itemsTotal += $item->getTotal();
        $this->items->add($item);
        $item->setOrder($this);

        $this->recalculateTotal();
    }

    /**
     * Removes an order item and updates the running items total.
     *
     * Idempotent — removing an item not present has no effect.
     *
     * @param OrderItemInterface $item The item to remove
     */
    public function removeItem(OrderItemInterface $item): void
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $this->itemsTotal -= $item->getTotal();
            $this->recalculateTotal();
            $item->setOrder(null);
        }
    }

    /**
     * Checks whether the given order item is part of this order.
     *
     * @param OrderItemInterface $item The item to look for
     *
     * @return bool True if the item is already in this order's items collection
     */
    public function hasItem(OrderItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    /**
     * Returns the sum of all item totals in the smallest currency unit (e.g. cents).
     *
     * @return int Items total before order-level adjustments
     */
    public function getItemsTotal(): int
    {
        return $this->itemsTotal;
    }

    /**
     * Iterates all items and recalculates the items total from scratch.
     *
     * Also triggers recalculateTotal() to update the grand total.
     *
     * @complexity O(n) where n is the number of items
     */
    public function recalculateItemsTotal(): void
    {
        $this->itemsTotal = 0;
        foreach ($this->items as $item) {
            $this->itemsTotal += $item->getTotal();
        }

        $this->recalculateTotal();
    }

    /**
     * Returns the grand total of the order in the smallest currency unit (e.g. cents).
     *
     * Grand total = itemsTotal + adjustmentsTotal (order-level discounts/surcharges).
     *
     * @return int Grand total as integer in smallest currency unit
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Returns the sum of quantities across all order items.
     *
     * @return int Total number of units ordered (sum of each item's quantity)
     *
     * @complexity O(n) where n is the number of items
     */
    public function getTotalQuantity(): int
    {
        $quantity = 0;

        foreach ($this->items as $item) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function getAdjustments(?string $type = null): Collection
    {
        if (null === $type) {
            return $this->adjustments;
        }

        return $this->adjustments->filter(fn (AdjustmentInterface $adjustment) => $type === $adjustment->getType());
    }

    public function getAdjustmentsRecursively(?string $type = null): Collection
    {
        $adjustments = clone $this->getAdjustments($type);
        foreach ($this->items as $item) {
            foreach ($item->getAdjustmentsRecursively($type) as $adjustment) {
                $adjustments->add($adjustment);
            }
        }

        return $adjustments;
    }

    public function addAdjustment(AdjustmentInterface $adjustment): void
    {
        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $this->addToAdjustmentsTotal($adjustment);
            $adjustment->setAdjustable($this);
            $this->recalculateAdjustmentsTotal();
        }
    }

    public function removeAdjustment(AdjustmentInterface $adjustment): void
    {
        if (!$adjustment->isLocked() && $this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $this->subtractFromAdjustmentsTotal($adjustment);
            $adjustment->setAdjustable(null);
            $this->recalculateAdjustmentsTotal();
        }
    }

    public function hasAdjustment(AdjustmentInterface $adjustment): bool
    {
        return $this->adjustments->contains($adjustment);
    }

    public function getAdjustmentsTotal(?string $type = null): int
    {
        if (null === $type) {
            return $this->adjustmentsTotal;
        }

        $total = 0;
        foreach ($this->getAdjustments($type) as $adjustment) {
            if (!$adjustment->isNeutral()) {
                $total += $adjustment->getAmount();
            }
        }

        return $total;
    }

    public function getAdjustmentsTotalRecursively(?string $type = null): int
    {
        $total = 0;
        foreach ($this->getAdjustmentsRecursively($type) as $adjustment) {
            if (!$adjustment->isNeutral()) {
                $total += $adjustment->getAmount();
            }
        }

        return $total;
    }

    public function removeAdjustments(?string $type = null): void
    {
        foreach ($this->getAdjustments($type) as $adjustment) {
            if ($adjustment->isLocked()) {
                continue;
            }

            $this->removeAdjustment($adjustment);
        }

        $this->recalculateAdjustmentsTotal();
    }

    public function removeAdjustmentsRecursively(?string $type = null): void
    {
        $this->removeAdjustments($type);
        foreach ($this->items as $item) {
            $item->removeAdjustmentsRecursively($type);
        }
    }

    public function recalculateAdjustmentsTotal(): void
    {
        $this->adjustmentsTotal = 0;

        foreach ($this->adjustments as $adjustment) {
            if (!$adjustment->isNeutral()) {
                $this->adjustmentsTotal += $adjustment->getAmount();
            }
        }

        $this->recalculateTotal();
    }

    public function canBeProcessed(): bool
    {
        return $this->state === self::STATE_CART;
    }

    /**
     * Items total + Adjustments total.
     */
    protected function recalculateTotal(): void
    {
        $this->total = $this->itemsTotal + $this->adjustmentsTotal;

        if ($this->total < 0) {
            $this->total = 0;
        }
    }

    protected function addToAdjustmentsTotal(AdjustmentInterface $adjustment): void
    {
        if (!$adjustment->isNeutral()) {
            $this->adjustmentsTotal += $adjustment->getAmount();
            $this->recalculateTotal();
        }
    }

    protected function subtractFromAdjustmentsTotal(AdjustmentInterface $adjustment): void
    {
        if (!$adjustment->isNeutral()) {
            $this->adjustmentsTotal -= $adjustment->getAmount();
            $this->recalculateTotal();
        }
    }
}
