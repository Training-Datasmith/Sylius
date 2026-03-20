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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Abstraction\State_Machine\State_Machine_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Channel_Pricing_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Order_Item_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Promotion_Coupon_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Core\Order_Checkout_Transitions;
use Sylius\Component\Core\Order_Payment_Transitions;
use Sylius\Component\Core\Order_Shipping_Transitions;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Order\Model\Order_Interface as BaseOrderInterface;
use Sylius\Component\Order\Modifier\Order_Item_Quantity_Modifier_Interface;
use Sylius\Component\Order\Order_Transitions;
use Sylius\Component\Payment\Model\Payment_Interface;
use Sylius\Component\Payment\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Payment_Transitions;
use Sylius\Component\Payment\Repository\Payment_Method_Repository_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Component\Shipping\Repository\Shipping_Method_Repository_Interface;
use Sylius\Component\Shipping\Shipment_Transitions;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Sylius\Resource\Generator\Randomness_Generator_Interface;
use Symfony\Component\Clock\Clock_Interface;
use Webmozart\Assert\Assert;
final readonly class Order_Context implements Context
{
    /**
     * @param FactoryInterface<OrderInterface> $orderFactory
     * @param FactoryInterface<AddressInterface> $addressFactory
     * @param FactoryInterface<CustomerInterface> $customerFactory
     * @param FactoryInterface<OrderItemInterface> $orderItemFactory
     * @param FactoryInterface<ShipmentInterface> $shipmentFactory
     * @param RepositoryInterface<CountryInterface> $countryRepository
     * @param CustomerRepositoryInterface<CustomerInterface> $customerRepository
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @param ShippingMethodRepositoryInterface<ShippingMethodInterface> $shippingMethodRepository
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $order_factory, private Factory_Interface $address_factory, private Factory_Interface $customer_factory, private Factory_Interface $order_item_factory, private Factory_Interface $shipment_factory, private State_Machine_Interface $state_machine, private Repository_Interface $country_repository, private Repository_Interface $customer_repository, private Order_Repository_Interface $order_repository, private Payment_Method_Repository_Interface $payment_method_repository, private Shipping_Method_Repository_Interface $shipping_method_repository, private Product_Variant_Resolver_Interface $variant_resolver, private Order_Item_Quantity_Modifier_Interface $item_quantity_modifier, private Object_Manager $object_manager, private Clock_Interface $clock, private Randomness_Generator_Interface $randomness_generator)
    {
    }
    #[Given('/^there is (?:a|another) (customer "[^"]+") that placed an order$/')]
    #[Given('/^there is (?:a|another) (customer "[^"]+") that placed (an order "[^"]+")$/')]
    #[Given('a customer :customer placed an order :orderNumber')]
    #[Given('the customer :customer has already placed an order :orderNumber')]
    #[Given('there is a customer :customer that placed an order :orderNumber in channel :channel')]
    #[Given('/^(this customer) placed (another order "[^"]+")$/')]
    public function there_is_customer_that_placed_order(Customer_Interface $customer, ?string $order_number = null, ?Channel_Interface $channel = null): void
    {
        $order = $this->create_order($customer, $order_number, $channel);
        $this->shared_storage->set('customer', $customer);
        $this->shared_storage->set('order', $order);
        $this->order_repository->add($order);
    }
    #[Given('there is a customer :customer that placed an order :orderNumber later')]
    public function there_is_a_customer_that_placed_an_order_later(Customer_Interface $customer, string $order_number): void
    {
        sleep(1);
        $this->there_is_customer_that_placed_order($customer, $order_number);
    }
    #[Given('/^there is a (customer "[^"]+") that placed order with ("[^"]+" product) to ("[^"]+" based billing address) with ("[^"]+" shipping method) and ("[^"]+" payment) method$/')]
    public function there_is_a_customer_that_placed_order_with_product_to_based_billing_address_with_shipping_method_and_payment_method(Customer_Interface $customer, Product_Interface $product, Address_Interface $address, Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        $this->place_order($product, $shipping_method, $address, $payment_method, $customer, 1);
        $this->object_manager->flush();
    }
    #[Given('/^there is a (customer "[^"]+") that placed order with ("[^"]+" product) to ("[^"]+" based billing address) with ("[^"]+" shipping method) and ("[^"]+" payment) method without completing it$/')]
    public function there_is_a_customer_that_placed_order_with_product_to_based_billing_address_with_shipping_method_and_payment_method_without_completing_it(Customer_Interface $customer, Product_Interface $product, Address_Interface $address, Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        $this->place_order($product, $shipping_method, $address, $payment_method, $customer, 1, false);
        $this->object_manager->flush();
    }
    #[Given('/^the guest customer placed order with ("[^"]+" product) for "([^"]+)" and ("[^"]+" based billing address) with ("[^"]+" shipping method) and ("[^"]+" payment)$/')]
    public function the_guest_customer_placed_order_with_for_and_based_shipping_address(Product_Interface $product, string $email, Address_Interface $address, Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        $customer = $this->create_customer($email);
        $this->customer_repository->add($customer);
        $this->place_order($product, $shipping_method, $address, $payment_method, $customer, 1);
        $this->object_manager->flush();
    }
    #[Given('/^the another guest customer placed order with ("[^"]+" product) for "([^"]+)" and ("[^"]+" based billing address) with ("[^"]+" shipping method) and ("[^"]+" payment)$/')]
    public function the_another_guest_customer_placed_order_with_for_and_based_shipping_address(Product_Interface $product, string $email, Address_Interface $address, Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        $customer = $this->create_customer($email);
        $this->customer_repository->add($customer);
        $this->shared_storage->set('customer', $customer);
        $this->place_order($product, $shipping_method, $address, $payment_method, $customer, 2);
        $this->object_manager->flush();
    }
    #[Given('a customer :customer added something to cart')]
    public function customer_started_checkout(Customer_Interface $customer): void
    {
        $cart = $this->create_cart($customer);
        $this->shared_storage->set('cart', $cart);
        $this->order_repository->add($cart);
    }
    #[Given('the customer :customer added :product product to the cart')]
    public function the_customer_added_product_to_the_cart(Customer_Interface $customer, Product_Interface $product): void
    {
        $cart = $this->create_cart($customer);
        $variant = $this->get_product_variant($product);
        $this->add_product_variants_to_order_with_channel_price($cart, $this->shared_storage->get('channel'), $variant, 1);
        $this->order_repository->add($cart);
        $this->shared_storage->set('cart', $cart);
    }
    #[Given('/^(I) placed (an order "[^"]+")$/')]
    public function i_placed_an_order(Shop_User_Interface $user, string $order_number): void
    {
        /** @var CustomerInterface $customer */
        $customer = $user->get_customer();
        $order = $this->create_order($customer, $order_number);
        $this->shared_storage->set('order', $order);
        $this->order_repository->add($order);
    }
    #[Given('/^the customer ("[^"]+" addressed it to "[^"]+", "[^"]+" "[^"]+" in the "[^"]+"(?:|, "[^"]+"))$/')]
    #[Given('/^I (addressed it to "[^"]+", "[^"]+", "[^"]+" "[^"]+" in the "[^"]+"(?:|, "[^"]+"))$/')]
    public function the_customer_addressed_it_to(Address_Interface $address): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $order->set_shipping_address($address);
        $this->object_manager->flush();
    }
    #[Given('the customer changed shipping address\' street to :street')]
    public function the_customer_changed_shipping_address_street_to(string $street): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $shipping_address = $order->get_shipping_address();
        $shipping_address->set_street($street);
        $this->object_manager->flush();
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_ADDRESS);
    }
    #[Given('/^the customer set the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    #[Given('/^for the billing address (of "[^"]+" in the "[^"]+", "[^"]+" "[^"]+", "[^"]+")$/')]
    #[Given('/^for the billing address (of "[^"]+" in the "[^"]+", "[^"]+" "([^"]+)", "[^"]+", "[^"]+")$/')]
    public function for_the_billing_address_of(Address_Interface $address): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $order->set_billing_address($address);
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_ADDRESS);
        $this->object_manager->flush();
    }
    #[Given('/^the customer ("[^"]+" addressed it to "[^"]+", "[^"]+" "[^"]+" in the "[^"]+") with identical billing address$/')]
    #[Given('/^I (addressed it to "[^"]+", "[^"]+", "[^"]+" "[^"]+" in the "[^"]+") with identical billing address$/')]
    public function the_customer_addressed_it_to_with_identical_billing_address(Address_Interface $address): void
    {
        $this->the_customer_addressed_it_to($address);
        $this->for_the_billing_address_of(clone $address);
    }
    #[Given('/^the customer chose ("[^"]+" shipping method) (to "[^"]+") with ("[^"]+" payment)$/')]
    #[Given('/^I chose ("[^"]+" shipping method) (to "[^"]+") with ("[^"]+" payment)$/')]
    public function the_customer_chose_shipping_to_with_payment(Shipping_Method_Interface $shipping_method, Address_Interface $address, Payment_Method_Interface $payment_method): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $this->checkout_using($order, $shipping_method, $address, $payment_method);
        $this->object_manager->flush();
    }
    #[Given('/^the customer chose ("[^"]+" shipping method) (to "[^"]+")$/')]
    public function the_customer_chose_shipping_to(Shipping_Method_Interface $shipping_method, Address_Interface $address): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $order->set_shipping_address($address);
        $order->set_billing_address(clone $address);
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_ADDRESS);
        foreach ($order->get_shipments() as $shipment) {
            $shipment->set_method($shipping_method);
        }
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_SELECT_SHIPPING);
        $this->object_manager->flush();
    }
    #[Given('/^the customer chose ("[^"]+" shipping method) with ("[^"]+" payment)$/')]
    #[Given('/^I chose ("[^"]+" shipping method) with ("[^"]+" payment)$/')]
    public function the_customer_chose_shipping_with_payment(Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $this->proceed_selecting_shipping_and_payment_method($order, $shipping_method, $payment_method);
        $this->complete_checkout($order);
        $this->object_manager->flush();
    }
    #[Given('/^the customer chose ("[^"]+" payment)$/')]
    public function the_customer_chose_payment(Payment_Method_Interface $payment_method): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        foreach ($order->get_payments() as $payment) {
            $payment->set_method($payment_method);
        }
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_SELECT_PAYMENT);
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_COMPLETE);
        $this->object_manager->flush();
    }
    #[Given('the customer bought a single :product')]
    #[Given('I bought a single :product')]
    public function the_customer_bought_single_product(Product_Interface $product, ?Channel_Interface $channel = null): void
    {
        $variant = $this->get_product_variant($product);
        $this->add_product_variant_to_order($variant, 1, $channel);
        $this->object_manager->flush();
    }
    #[Given('the customer bought another :product with separate :shippingMethod shipment')]
    public function the_customer_bought_another_product_with_separate_shipment(Product_Interface $product, Shipping_Method_Interface $shipping_method): void
    {
        $variant = $this->get_product_variant($product);
        $this->add_product_variant_to_order($variant, 1);
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        /** @var ShipmentInterface $shipment */
        $shipment = $this->shipment_factory->create_new();
        $shipment->set_method($shipping_method);
        $shipment->set_order($order);
        $order->add_shipment($shipment);
        $this->object_manager->flush();
    }
    #[Given('/^the customer bought ((?:a|an) "[^"]+") and ((?:a|an) "[^"]+")$/')]
    #[Given('/^I bought ((?:a|an) "[^"]+") and ((?:a|an) "[^"]+")$/')]
    public function the_customer_bought_product_and_product(Product_Interface $product, Product_Interface $second_product): void
    {
        $this->the_customer_bought_single_product($product);
        $this->the_customer_bought_single_product($second_product);
    }
    #[Given('/^the customer bought (\d+) ("[^"]+" products)$/')]
    public function the_customer_bought_several_products(int $quantity, Product_Interface $product): void
    {
        $variant = $this->get_product_variant($product);
        $this->add_product_variant_to_order($variant, $quantity);
        $this->object_manager->flush();
    }
    #[Given('/^the customer bought ([^"]+) units of ("[^"]+" variant of product "[^"]+")$/')]
    public function the_customer_bought_several_variants_of_product(int $quantity, Product_Variant_Interface $variant): void
    {
        $this->add_product_variant_to_order($variant, $quantity);
        $this->object_manager->flush();
    }
    #[Given('/^the customer bought a single ("[^"]+" variant of product "[^"]+")$/')]
    #[Given('/^the customer also bought a ("[^"]+" variant of product "[^"]+")$/')]
    public function the_customer_bought_single_product_variant(Product_Variant_Interface $product_variant): void
    {
        $this->add_product_variant_to_order($product_variant);
        $this->object_manager->flush();
    }
    #[Given('the customer bought a single :product using :coupon coupon')]
    #[Given('I bought a single :product using :coupon coupon')]
    public function the_customer_bought_single_using(Product_Interface $product, Promotion_Coupon_Interface $coupon): void
    {
        $variant = $this->get_product_variant($product);
        $order = $this->add_product_variant_to_order($variant);
        $order->set_promotion_coupon($coupon);
        $this->object_manager->flush();
    }
    #[Given('I used :coupon coupon')]
    public function i_used_coupon(Promotion_Coupon_Interface $coupon): void
    {
        $order = $this->shared_storage->get('order');
        $order->set_promotion_coupon($coupon);
        $this->object_manager->flush();
    }
    #[Given('/^(I) have already placed (\d+) orders choosing ("[^"]+" product), ("[^"]+" shipping method) (to "[^"]+") with ("[^"]+" payment)$/')]
    public function i_have_already_placed_order_nth_times(Shop_User_Interface $user, int $number_of_orders, Product_Interface $product, Shipping_Method_Interface $shipping_method, Address_Interface $address, Payment_Method_Interface $payment_method): void
    {
        /** @var CustomerInterface $customer */
        $customer = $user->get_customer();
        for ($i = 0; $i < $number_of_orders; ++$i) {
            $this->place_order($product, $shipping_method, $address, $payment_method, $customer, $i);
        }
        $this->object_manager->flush();
    }
    #[Given('there is an :orderNumber order with :product product')]
    #[Given('there is an :orderNumber order with :product product in this channel')]
    #[Given('there is an :orderNumber order with :product product in :channel channel')]
    #[Given('there is a :state :orderNumber order with :product product')]
    public function there_is_a_order_with_product(string $order_number, Product_Interface $product, ?string $state = null, ?Channel_Interface $channel = null): void
    {
        $order = $this->create_order($this->create_or_provide_customer('amba@fatima.org'), $order_number, $channel);
        $this->shared_storage->set('order', $order);
        $this->the_customer_bought_single_product($product, $channel);
        $this->create_shipping_payment_methods_and_address();
        if ($state !== null) {
            foreach ($this->get_target_payment_transitions($state) as $transition) {
                $this->apply_payment_transition_on_order($order, $transition);
            }
        }
        $this->order_repository->add($order);
    }
    #[Given('there is an :orderNumber order with :product product ordered later')]
    public function there_is_an_order_with_product_ordered_later(string $order_number, Product_Interface $product): void
    {
        sleep(1);
        $this->there_is_a_order_with_product($order_number, $product);
    }
    /**
     * @throws \Exception
     */
    #[Given('/^(this customer) has(?:| also) placed (an order "[^"]+") at "([^"]+)"$/')]
    public function this_customer_has_placed_an_order_at_date(Customer_Interface $customer, string $number, string $checkout_completed_at): void
    {
        $order = $this->create_order($customer, $number);
        $order->set_checkout_completed_at(new \DateTime($checkout_completed_at));
        $order->set_state(Base_Order_Interface::STATE_NEW);
        $this->order_repository->add($order);
    }
    #[Given('/^(this customer) has(?:| also) placed (an order "[^"]+") on a (channel "[^"]+")$/')]
    public function this_customer_has_placed_an_order_on_a_channel(Customer_Interface $customer, string $number, Channel_Interface $channel): void
    {
        $order = $this->create_order($customer, $number, $channel);
        $order->set_state(Base_Order_Interface::STATE_NEW);
        $this->order_repository->add($order);
        $this->shared_storage->set('order', $order);
    }
    #[Given('/^(this customer) has(?:| also) started checkout on a (channel "[^"]+")$/')]
    public function this_customer_has_started_checkout_on_a_channel(Customer_Interface $customer, Channel_Interface $channel): void
    {
        $order = $this->create_order($customer, null, $channel);
        $this->order_repository->add($order);
        $this->shared_storage->set('order', $order);
    }
    #[Given('/^(customer "[^"]+"|this customer) has(?:| also) placed (\d+) orders on the ("[^"]+" channel) in each buying (\d+) ("[^"]+" products?)$/')]
    public function this_customer_placed_orders_on_channel_buying_products(Customer_Interface $customer, int $order_count, Channel_Interface $channel, int $product_count, Product_Interface $product): void
    {
        $this->create_orders_for_customer($customer, $order_count, $channel, $product_count, $product);
    }
    #[Given('/^(customer "[^"]+"|this customer) has(?:| also) fulfilled (\d+) orders placed on the ("[^"]+" channel) in each buying (\d+) ("[^"]+" products?)$/')]
    public function this_customer_fulfilled_orders_placed_on_channel_buying_products(Customer_Interface $customer, int $order_count, Channel_Interface $channel, int $product_count, Product_Interface $product): void
    {
        $this->create_orders_for_customer($customer, $order_count, $channel, $product_count, $product, true);
    }
    #[Given('/^(\d+) new customers have added products to the cart for total of ("[^"]+")$/')]
    public function customers_have_added_products_to_the_cart_for_total_of(int $number_of_customers, int $total): void
    {
        $customers = $this->generate_customers($number_of_customers);
        $sample_product_variant = $this->shared_storage->get('variant');
        for ($i = 0; $i < $number_of_customers; ++$i) {
            $order = $this->create_cart($customers[random_int(0, $number_of_customers - 1)]);
            $price = $i === $number_of_customers - 1 ? $total : random_int(1, $total);
            $total -= $price;
            $this->add_variant_with_price_to_order($order, $sample_product_variant, $price);
            $this->object_manager->persist($order);
        }
        $this->object_manager->flush();
    }
    #[Given('/^a single customer has placed an order for total of ("[^"]+")$/')]
    public function a_single_customer_has_placed_an_order_for_total_of(int $total): void
    {
        $this->create_orders(numberOfCustomers: 1, numberOfOrders: 1, total: $total);
    }
    #[Given('/^(\d+) (?:|more )new customers have placed (\d+) orders for total of ("[^"]+")$/')]
    public function customers_have_placed_orders_for_total_of(int $number_of_customers, int $number_of_orders, int $total): void
    {
        $this->create_orders($number_of_customers, $number_of_orders, $total);
    }
    #[Given('/^(\d+) new customers have fulfilled (\d+) orders placed for total of ("[^"]+")$/')]
    public function customers_have_fulfilled_orders_placed_for_total_of(int $number_of_customers, int $number_of_orders, int $total): void
    {
        $this->create_orders($number_of_customers, $number_of_orders, $total, true);
    }
    #[Given('/^(\d+) (?:|more )new customers have placed (\d+) orders for total of ("[^"]+") mostly ("[^"]+" product)$/')]
    public function customers_have_placed_orders_for_total_of_mostly_product(int $number_of_customers, int $number_of_orders, int $total, Product_Interface $product): void
    {
        $this->create_orders_with_product($number_of_customers, $number_of_orders, $total, $product);
    }
    #[Given('/^(\d+) (?:|more )new customers have fulfilled (\d+) orders placed for total of ("[^"]+") mostly ("[^"]+" product)$/')]
    public function customers_have_fulfilled_orders_placed_for_total_of_mostly_product(int $number_of_customers, int $number_of_orders, int $total, Product_Interface $product): void
    {
        $this->create_orders_with_product($number_of_customers, $number_of_orders, $total, $product, true);
    }
    #[Given('/^(\d+) (?:|more )new customers have paid (\d+) orders placed for total of ("[^"]+")$/')]
    public function more_customers_have_paid_orders_placed_for_total_of(int $number_of_customers, int $number_of_orders, int $total): void
    {
        $this->create_paid_orders($number_of_customers, $number_of_orders, $total);
    }
    #[Given('/^(this customer) has(?:| also) placed (an order "[^"]+") buying a single ("[^"]+" product) for ("[^"]+") on the ("[^"]+" channel)$/')]
    public function customer_has_placed_an_order_buying_a_single_product_for_on_the_channel(Customer_Interface $customer, string $order_number, Product_Interface $product, int $price, Channel_Interface $channel): void
    {
        $order = $this->create_order($customer, $order_number, $channel);
        $order->set_state(Base_Order_Interface::STATE_NEW);
        $variant = $this->get_product_variant($product);
        $this->add_variant_with_price_to_order($order, $variant, $price);
        $this->order_repository->add($order);
        $this->shared_storage->set('order', $order);
    }
    #[Given('/^(this order) is already paid$/')]
    #[Given('the order :order is already paid')]
    public function this_order_is_already_paid(Order_Interface $order): void
    {
        $this->apply_payment_transition_on_order($order, Payment_Transitions::TRANSITION_COMPLETE);
        $this->object_manager->flush();
    }
    #[Given('/^(this order) has been refunded$/')]
    #[Given('the customer has refunded the order with number :order')]
    public function this_order_has_been_refunded(Order_Interface $order): void
    {
        $this->apply_payment_transition_on_order($order, Payment_Transitions::TRANSITION_REFUND);
        $this->object_manager->flush();
    }
    #[Given('/^the customer cancelled (this order)$/')]
    #[Given('/^(this order) was cancelled$/')]
    #[Given('the order :order was cancelled')]
    #[Given('/^I cancelled (this order)$/')]
    public function the_customer_cancelled_this_order(Order_Interface $order): void
    {
        $this->state_machine->apply($order, Order_Transitions::GRAPH, Order_Transitions::TRANSITION_CANCEL);
        $this->object_manager->flush();
    }
    #[Given('/^I cancelled my last order$/')]
    public function the_customer_cancelled_my_last_order(): void
    {
        $order = $this->shared_storage->get('order');
        $this->state_machine->apply($order, Order_Transitions::GRAPH, Order_Transitions::TRANSITION_CANCEL);
        $this->object_manager->flush();
    }
    #[Given('/^(this order) has already been shipped$/')]
    #[Given('the order :order is already shipped')]
    public function this_order_has_already_been_shipped(Order_Interface $order): void
    {
        $this->apply_shipment_transition_on_order($order, Shipment_Transitions::TRANSITION_SHIP);
        $this->object_manager->flush();
    }
    #[When('the customer used coupon :coupon')]
    public function the_customer_used_coupon(Promotion_Coupon_Interface $coupon): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $order->set_promotion_coupon($coupon);
        $this->object_manager->flush();
    }
    #[Given('the order :order has been placed in :localeCode locale')]
    public function the_order_has_been_placed_in_locale(Order_Interface $order, string $locale_code): void
    {
        $order->set_locale_code($locale_code);
        $this->object_manager->flush();
    }
    #[Given('the customer completed the order')]
    public function the_customer_completed_the_order(): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $this->complete_checkout($order);
        $this->object_manager->flush();
    }
    #[Given('the :product product\'s inventory has become tracked with :numberOfItems items')]
    public function the_product_s_inventory_has_became_tracked_with_items(Product_Interface $product, int $number_of_items): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $product->get_variants()->first();
        $product_variant->set_tracked(true);
        $product_variant->set_on_hand($number_of_items);
        $this->object_manager->flush();
    }
    private function apply_shipment_transition_on_order(Order_Interface $order, string $transition): void
    {
        foreach ($order->get_shipments() as $shipment) {
            $this->state_machine->apply($shipment, Shipment_Transitions::GRAPH, $transition);
        }
    }
    private function apply_payment_transition_on_order(Order_Interface $order, string $transition): void
    {
        foreach ($order->get_payments() as $payment) {
            $this->state_machine->apply($payment, Payment_Transitions::GRAPH, $transition);
        }
    }
    private function apply_transition_on_order_checkout(Order_Interface $order, string $transition): void
    {
        $this->state_machine->apply($order, Order_Checkout_Transitions::GRAPH, $transition);
    }
    private function add_product_variant_to_order(Product_Variant_Interface $product_variant, int $quantity = 1, ?Channel_Interface $channel = null): Order_Interface
    {
        $order = $this->shared_storage->get('order');
        $this->add_product_variants_to_order_with_channel_price($order, $channel ?? $this->shared_storage->get('channel'), $product_variant, $quantity);
        return $order;
    }
    private function add_product_variants_to_order_with_channel_price(Order_Interface $order, Channel_Interface $channel, Product_Variant_Interface $product_variant, int $quantity = 1): void
    {
        /** @var OrderItemInterface $item */
        $item = $this->order_item_factory->create_new();
        $item->set_variant($product_variant);
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($channel);
        $item->set_unit_price($channel_pricing->get_price());
        $this->item_quantity_modifier->modify($item, $quantity);
        $order->add_item($item);
    }
    private function create_order(Customer_Interface $customer, ?string $number = null, ?Channel_Interface $channel = null): Order_Interface
    {
        $order = $this->create_cart($customer, $channel);
        $order->set_token_value($this->generate_token());
        if (null !== $number) {
            $order->set_number($number);
        }
        $order->complete_checkout();
        return $order;
    }
    private function create_cart(Customer_Interface $customer, ?Channel_Interface $channel = null): Order_Interface
    {
        /** @var OrderInterface $order */
        $order = $this->order_factory->create_new();
        $customer->get_user() === null ? $order->set_customer($customer) : $order->set_customer_with_authorization($customer);
        $order->set_channel($channel ?? $this->shared_storage->get('channel'));
        $order->set_locale_code($this->shared_storage->get('locale')->get_code());
        $order->set_currency_code($order->get_channel()->get_base_currency()->get_code());
        return $order;
    }
    private function create_customer(string $email): Customer_Interface
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customer_factory->create_new();
        $customer->set_email($email);
        $customer->set_first_name('John');
        $customer->set_last_name('Doe');
        return $customer;
    }
    private function create_or_provide_customer(string $email): Customer_Interface
    {
        /** @var CustomerInterface|null $customer */
        $customer = $this->customer_repository->find_one_by(['email' => $email]);
        return $customer ?? $this->create_customer($email);
    }
    /**
     * @return CustomerInterface[]
     */
    private function generate_customers(int $count): array
    {
        $customers = [];
        for ($i = 0; $i < $count; ++$i) {
            /** @var CustomerInterface $customer */
            $customer = $this->customer_factory->create_new();
            $customer->set_email(sprintf('john%s@doe.com', uniqid()));
            $customer->set_firstname('John');
            $customer->set_lastname('Doe' . $i);
            $customer->set_created_at($this->clock->now());
            $customers[] = $customer;
            $this->customer_repository->add($customer);
        }
        return $customers;
    }
    private function checkout_using(Order_Interface $order, Shipping_Method_Interface $shipping_method, Address_Interface $address, Payment_Method_Interface $payment_method, bool $complete_order = true): void
    {
        $order->set_shipping_address($address);
        $order->set_billing_address(clone $address);
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_ADDRESS);
        $this->proceed_selecting_shipping_and_payment_method($order, $shipping_method, $payment_method);
        if ($complete_order) {
            $this->complete_checkout($order);
        }
    }
    private function complete_checkout(Order_Interface $order): void
    {
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_COMPLETE);
    }
    private function create_shipping_payment_methods_and_address(): void
    {
        /** @var AddressInterface $address */
        $address = $this->address_factory->create_new();
        $address->set_city('Wawa');
        $address->set_country_code($this->country_repository->find_one_by([])->get_code());
        $address->set_first_name('Jon');
        $address->set_last_name('Doe');
        $address->set_postcode('000');
        $address->set_street('Happy');
        $this->the_customer_addressed_it_to_with_identical_billing_address($address);
        $shipping_method = $this->shipping_method_repository->find_one_by([]);
        Assert::not_null($shipping_method);
        $payment_method = $this->payment_method_repository->find_one_by([]);
        Assert::not_null($payment_method);
        $this->the_customer_chose_shipping_with_payment($shipping_method, $payment_method);
    }
    private function proceed_selecting_shipping_and_payment_method(Order_Interface $order, Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        foreach ($order->get_shipments() as $shipment) {
            $shipment->set_method($shipping_method);
        }
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_SELECT_SHIPPING);
        $payment = $order->get_last_payment(Payment_Interface::STATE_CART);
        $payment->set_method($payment_method);
        $this->apply_transition_on_order_checkout($order, Order_Checkout_Transitions::TRANSITION_SELECT_PAYMENT);
    }
    private function add_variant_with_price_to_order(Order_Interface $order, Product_Variant_Interface $variant, int $price): void
    {
        /** @var OrderItemInterface $item */
        $item = $this->order_item_factory->create_new();
        $item->set_variant($variant);
        $item->set_unit_price($price);
        $this->item_quantity_modifier->modify($item, 1);
        $order->add_item($item);
    }
    private function create_orders(int $number_of_customers, int $number_of_orders, int $total, bool $is_fulfilled = false): void
    {
        $customers = $this->generate_customers($number_of_customers);
        $sample_product_variant = $this->shared_storage->get('variant');
        for ($i = 0; $i < $number_of_orders; ++$i) {
            $order = $this->create_order($customers[random_int(0, $number_of_customers - 1)], '#' . uniqid());
            $this->state_machine->apply($order, Order_Transitions::GRAPH, Order_Transitions::TRANSITION_CREATE);
            $this->apply_payment_transition_on_order($order, Payment_Transitions::TRANSITION_COMPLETE);
            $price = $i === $number_of_orders - 1 ? $total : random_int(1, $total);
            $total -= $price;
            $this->add_variant_with_price_to_order($order, $sample_product_variant, $price);
            if ($is_fulfilled) {
                $this->pay_order($order);
                $this->ship_order($order);
            }
            $order->set_checkout_completed_at($this->clock->now());
            $this->object_manager->persist($order);
            $this->shared_storage->set('order', $order);
        }
        $this->object_manager->flush();
    }
    private function create_paid_orders(int $number_of_customers, int $number_of_orders, int $total): void
    {
        $customers = $this->generate_customers($number_of_customers);
        $sample_product_variant = $this->shared_storage->get('variant');
        for ($i = 0; $i < $number_of_orders; ++$i) {
            $order = $this->create_order($customers[random_int(0, $number_of_customers - 1)], '#' . uniqid());
            $this->state_machine->apply($order, Order_Transitions::GRAPH, Order_Transitions::TRANSITION_CREATE);
            $this->apply_payment_transition_on_order($order, Payment_Transitions::TRANSITION_COMPLETE);
            $price = $i === $number_of_orders - 1 ? $total : random_int(1, $total);
            $total -= $price;
            $this->add_variant_with_price_to_order($order, $sample_product_variant, $price);
            $this->pay_order($order);
            $order->set_checkout_completed_at($this->clock->now());
            $this->object_manager->persist($order);
            $this->shared_storage->set('order', $order);
        }
        $this->object_manager->flush();
    }
    private function create_orders_with_product(int $number_of_customers, int $number_of_orders, int $total, Product_Interface $product, bool $is_fulfilled = false): void
    {
        $customers = $this->generate_customers($number_of_customers);
        /** @var ProductVariantInterface $sampleProductVariant */
        $sample_product_variant = $product->get_variants()->first();
        for ($i = 0; $i < $number_of_orders; ++$i) {
            $order = $this->create_order($customers[random_int(0, $number_of_customers - 1)], '#' . uniqid(), $product->get_channels()->first());
            $this->state_machine->apply($order, Order_Transitions::GRAPH, Order_Transitions::TRANSITION_CREATE);
            $this->apply_payment_transition_on_order($order, Payment_Transitions::TRANSITION_COMPLETE);
            $price = $i === $number_of_orders - 1 ? $total : random_int(1, $total);
            $total -= $price;
            $this->add_variant_with_price_to_order($order, $sample_product_variant, $price);
            if ($is_fulfilled) {
                $this->pay_order($order);
                $this->ship_order($order);
            }
            $order->set_checkout_completed_at($this->clock->now());
            $this->object_manager->persist($order);
        }
        $this->object_manager->flush();
    }
    private function create_orders_for_customer(Customer_Interface $customer, int $order_count, Channel_Interface $channel, int $product_count, Product_Interface $product, bool $is_fulfilled = false): void
    {
        $variant = $this->get_product_variant($product);
        for ($i = 0; $i < $order_count; ++$i) {
            $order = $this->create_order($customer, uniqid('#'), $channel);
            $this->add_product_variants_to_order_with_channel_price($order, $channel, $variant, $product_count);
            $order->set_state($is_fulfilled ? Base_Order_Interface::STATE_FULFILLED : Base_Order_Interface::STATE_NEW);
            $order->set_checkout_completed_at($this->clock->now());
            $this->object_manager->persist($order);
        }
        $this->object_manager->flush();
    }
    /** @return array<array-key, string> */
    private function get_target_payment_transitions(string $state): array
    {
        $state = strtolower($state);
        $transitions = ['new' => [], 'processing' => [Payment_Transitions::TRANSITION_PROCESS], 'completed' => [Payment_Transitions::TRANSITION_COMPLETE], 'cancelled' => [Payment_Transitions::TRANSITION_CANCEL], 'failed' => [Payment_Transitions::TRANSITION_FAIL], 'refunded' => [Payment_Transitions::TRANSITION_COMPLETE, Payment_Transitions::TRANSITION_REFUND]];
        return $transitions[$state];
    }
    private function place_order(Product_Interface $product, Shipping_Method_Interface $shipping_method, Address_Interface $address, Payment_Method_Interface $payment_method, Customer_Interface $customer, int $number, bool $complete_order = true): void
    {
        $variant = $this->get_product_variant($product);
        $channel_pricing = $variant->get_channel_pricing_for_channel($this->shared_storage->get('channel'));
        /** @var OrderItemInterface $item */
        $item = $this->order_item_factory->create_new();
        $item->set_variant($variant);
        $item->set_unit_price($channel_pricing->get_price());
        $this->item_quantity_modifier->modify($item, 1);
        $order = $this->create_order($customer, '00000' . $number);
        $order->add_item($item);
        $this->checkout_using($order, $shipping_method, clone $address, $payment_method, $complete_order);
        if ($complete_order) {
            $this->apply_payment_transition_on_order($order, Payment_Transitions::TRANSITION_COMPLETE);
        }
        $this->object_manager->persist($order);
        $this->shared_storage->set('order', $order);
        if (!$complete_order) {
            $this->shared_storage->set('cart_token', $order->get_token_value());
        }
    }
    private function get_product_variant(Product_Interface $product): Product_Variant_Interface
    {
        /** @var ProductVariantInterface|null $variant */
        $variant = $this->variant_resolver->get_variant($product);
        if ($variant === null) {
            throw new \RuntimeException(sprintf('Product "%s" has no variant', $product->get_code()));
        }
        return $variant;
    }
    private function ship_order(Order_Interface $order): void
    {
        $this->state_machine->apply($order, Order_Shipping_Transitions::GRAPH, Order_Shipping_Transitions::TRANSITION_SHIP);
    }
    private function pay_order(Order_Interface $order): void
    {
        $this->state_machine->apply($order, Order_Payment_Transitions::GRAPH, Order_Payment_Transitions::TRANSITION_PAY);
    }
    private function generate_token(): string
    {
        do {
            $token = $this->randomness_generator->generate_uri_safe_string(10);
        } while ($this->order_repository->find_one_by(['tokenValue' => $token]) !== null);
        return $token;
    }
}