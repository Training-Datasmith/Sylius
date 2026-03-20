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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Adjustment_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Core\Updater\Unpaid_Orders_State_Updater_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Orders_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Order_Repository_Interface $order_repository, private Repository_Interface $order_item_repository, private Repository_Interface $address_repository, private Repository_Interface $adjustment_repository, private Object_Manager $order_manager, private Product_Variant_Resolver_Interface $variant_resolver, private Unpaid_Orders_State_Updater_Interface $unpaid_orders_state_updater)
    {
    }
    #[When('I delete the order :order')]
    public function i_delete_the_order(Order_Interface $order): void
    {
        $adjustments_id = [];
        foreach ($order->get_adjustments() as $adjustment) {
            $adjustments_id[] = $adjustment->get_id();
        }
        $this->shared_storage->set('deleted_adjustments', $adjustments_id);
        $this->shared_storage->set('deleted_addresses', [$order->get_shipping_address()->get_id(), $order->get_billing_address()->get_id()]);
        $this->shared_storage->set('order_id', $order->get_id());
        $this->order_repository->remove($order);
    }
    #[When('I view the summary of the order :order')]
    public function i_view_the_summary_of_the_order(Order_Interface $order): void
    {
        $this->shared_storage->set('order', $order);
    }
    #[Then('this order should not exist in the registry')]
    public function order_should_not_exist_in_the_registry(): void
    {
        $order_id = $this->shared_storage->get('order_id');
        $order = $this->order_repository->find($order_id);
        Assert::null($order);
    }
    #[Then('the order item with product :product should not exist')]
    public function order_item_should_not_exist_in_the_registry(Product_Interface $product): void
    {
        $order_items = $this->order_item_repository->find_by(['variant' => $this->variant_resolver->get_variant($product)]);
        Assert::same($order_items, []);
    }
    #[Then('billing and shipping addresses of this order should not exist')]
    public function addresses_should_not_exist_in_the_registry(): void
    {
        $addresses = $this->shared_storage->get('deleted_addresses');
        $addresses = $this->address_repository->find_by(['id' => $addresses]);
        Assert::same($addresses, []);
    }
    #[Then('adjustments of this order should not exist')]
    public function adjustment_should_not_exist_in_the_registry(): void
    {
        $adjustments = $this->shared_storage->get('deleted_adjustments');
        $adjustments = $this->adjustment_repository->find_by(['id' => $adjustments]);
        Assert::same($adjustments, []);
    }
    #[Given('/^(this order) has not been paid for (\d+) (day|days|hour|hours)$/')]
    public function this_order_has_not_been_paid_for_days(Order_Interface $order, string $amount, string $time): void
    {
        $order->set_checkout_completed_at(new \DateTime('-' . $amount . ' ' . $time));
        $this->order_manager->flush();
        $this->unpaid_orders_state_updater->cancel();
    }
    #[Given('/^the (order "[^"]+") has not been paid for (\d+) (day|days)$/')]
    public function order_with_number_has_not_been_paid_for_days(Order_Interface $order, int $amount, string $days): void
    {
        $order->set_checkout_completed_at(new \DateTime(sprintf('-%d %s', $amount, $days)));
        $this->order_manager->flush();
    }
    #[Then('/^(this order) should be automatically cancelled$/')]
    public function this_order_should_be_automatically_cancelled(Order_Interface $order): void
    {
        Assert::same($order->get_state(), Order_Interface::STATE_CANCELLED);
    }
    #[Then('/^(this order) should not be cancelled$/')]
    public function this_order_should_not_be_cancelled(Order_Interface $order): void
    {
        Assert::not_same($order->get_state(), Order_Interface::STATE_CANCELLED);
    }
    #[Then('/^(the order)\'s items total should be ("[^"]+")$/')]
    public function the_orders_items_total_should_be(Order_Interface $order, int $items_total): void
    {
        Assert::same($order->get_items_total(), $items_total);
    }
    #[Then('/^there should be a shipping charge ("[^"]+") for "([^"]+)" method$/')]
    public function there_should_be_a_shipping_charge_for_method(int $shipping_charge, string $shipping_method_name): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        foreach ($order->get_adjustments(Adjustment_Interface::SHIPPING_ADJUSTMENT) as $adjustment) {
            if ($adjustment->get_amount() === $shipping_charge && $adjustment->get_details()['shippingMethodName'] === $shipping_method_name) {
                return;
            }
        }
        throw new \DomainException('The given order has no shipping adjustment with proper amount and method');
    }
    #[Then('/^there should be a shipping tax ("[^"]+") for "([^"]+)" method$/')]
    public function there_should_be_a_shipping_tax_for_method(int $shipping_tax, string $shipping_method_name): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        foreach ($order->get_adjustments(Adjustment_Interface::TAX_ADJUSTMENT) as $adjustment) {
            if ($adjustment->get_amount() === $shipping_tax && $adjustment->get_details()['shippingMethodName'] === $shipping_method_name) {
                return;
            }
        }
        throw new \DomainException('The given order has no shipping adjustment with proper amount and method');
    }
    #[Then('/^(the order)\'s shipping total should be ("[^"]+")$/')]
    public function the_orders_shipping_total_should_be(Order_Interface $order, int $shipping_total): void
    {
        Assert::same($order->get_shipping_total(), $shipping_total);
    }
    #[Then('/^(the order)\'s tax total should be ("[^"]+")$/')]
    public function the_orders_tax_total_should_be(Order_Interface $order, int $tax_total): void
    {
        Assert::same($order->get_tax_total(), $tax_total);
    }
    #[Then('/^(the order)\'s total should be ("[^"]+")$/')]
    public function the_orders_total_should_be(Order_Interface $order, int $total): void
    {
        Assert::same($order->get_total(), $total);
    }
}