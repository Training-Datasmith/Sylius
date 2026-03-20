<?php

declare(strict_types=1);

/**
 * Example: Creating and processing an order in Sylius
 *
 * Demonstrates how the Order aggregate works and how to programmatically
 * create an order with items, apply promotions, and transition it through
 * the state machine.
 *
 * In a real application these services are injected by the Symfony DI container.
 */

namespace App\Examples;

// -------------------------------------------------------------------------
// 1. Creating an order programmatically (e.g. in a fixture or import)
// -------------------------------------------------------------------------

use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;

$order = new Order();
$order->setLocaleCode('en_US');
$order->setCurrencyCode('USD');

$item = new OrderItem();
$item->setVariant($productVariant);     // ProductVariantInterface
$item->setUnitPrice(2999);              // 29.99 USD in cents

// Add item units (one per physical unit purchased)
for ($i = 0; $i < 2; $i++) {
    $unit = new OrderItemUnit($item);
    $item->addUnit($unit);
}

$order->addItem($item);

// -------------------------------------------------------------------------
// 2. Processing the order (taxes, shipping, promotions)
// -------------------------------------------------------------------------
// Inject Sylius\Component\Order\Processor\OrderProcessorInterface
$orderProcessor->process($order);

echo 'Items total:     ' . $order->getItemsTotal() . ' cents' . PHP_EOL;
echo 'Adjustments:     ' . $order->getAdjustmentsTotal() . ' cents' . PHP_EOL;
echo 'Grand total:     ' . $order->getTotal() . ' cents' . PHP_EOL;

// -------------------------------------------------------------------------
// 3. Completing checkout via state machine
// -------------------------------------------------------------------------
// Inject SM\Factory\FactoryInterface (WinzouStateMachineBundle)
$stateMachine = $factory->get($order, 'sylius_order');
$stateMachine->apply('create');   // cart → new

echo 'Order state: ' . $order->getState() . PHP_EOL;  // "new"

// -------------------------------------------------------------------------
// 4. Checking if payment method selection is required
// -------------------------------------------------------------------------
// Inject Sylius\Component\Core\Checker\OrderPaymentMethodSelectionRequirementCheckerInterface
$requiresPaymentSelection = $checker->isPaymentMethodSelectionRequired($order);
if ($requiresPaymentSelection) {
    echo 'Redirect customer to payment method selection step.' . PHP_EOL;
} else {
    echo 'Payment step can be skipped (free or single-method order).' . PHP_EOL;
}
