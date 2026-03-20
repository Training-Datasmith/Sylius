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
use Sylius\Behat\Context\Setup\Checkout\Payment_Context;
use Sylius\Behat\Context\Setup\Checkout\Shipping_Context;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Choose_Payment_Method;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Choose_Shipping_Method;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Update_Cart;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Context implements Context
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param RepositoryInterface<ShippingMethodInterface> $shippingMethodRepository
     * @param RepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @param FactoryInterface<AddressInterface> $addressFactory
     */
    public function __construct(private Order_Repository_Interface $order_repository, private Repository_Interface $shipping_method_repository, private Repository_Interface $payment_method_repository, private Message_Bus_Interface $command_bus, private Factory_Interface $address_factory, private Shared_Storage_Interface $shared_storage, private Shipping_Context $checkout_shipping_context, private Payment_Context $checkout_payment_context)
    {
    }
    #[Given('I chose :shippingMethod shipping method and :paymentMethod payment method')]
    public function i_proceed_order_with_shipping_method_and_payment(Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        $this->checkout_shipping_context->choose_shipping_method($shipping_method);
        $this->checkout_payment_context->choose_payment_method($payment_method);
    }
    #[Given('I have proceeded through checkout process in the :localeCode locale with email :email')]
    public function i_have_proceeded_through_checkout_process_in_the_locale_with_email(string $locale_code, string $email): void
    {
        $cart_token = $this->shared_storage->get('cart_token');
        $this->shared_storage->set('locale_code', $locale_code);
        /** @var OrderInterface|null $cart */
        $cart = $this->order_repository->find_cart_by_token_value($cart_token);
        Assert::not_null($cart);
        $cart->set_locale_code($locale_code);
        $command = new Update_Cart(orderTokenValue: $cart_token, email: $email, billingAddress: $this->get_default_address());
        $this->command_bus->dispatch($command);
        $this->complete_checkout($cart);
    }
    #[Given('I have proceeded through checkout process')]
    public function i_have_proceeded_through_checkout_process(): void
    {
        $cart_token = $this->shared_storage->get('cart_token');
        /** @var OrderInterface|null $cart */
        $cart = $this->order_repository->find_cart_by_token_value($cart_token);
        Assert::not_null($cart);
        $command = new Update_Cart(orderTokenValue: $cart_token, email: null, billingAddress: $this->get_default_address());
        $this->command_bus->dispatch($command);
        $this->complete_checkout($cart);
    }
    private function get_default_address(): Address_Interface
    {
        /** @var AddressInterface $address */
        $address = $this->address_factory->create_new();
        $address->set_city('New York');
        $address->set_street('Wall Street');
        $address->set_postcode('00-001');
        $address->set_country_code('US');
        $address->set_first_name('Richy');
        $address->set_last_name('Rich');
        return $address;
    }
    private function complete_checkout(Order_Interface $order, ?Shipping_Method_Interface $shipping_method = null, ?Payment_Method_Interface $payment_method = null): void
    {
        $shipping_method = $shipping_method ?: $this->shipping_method_repository->find_one_by([]);
        /** @var ShipmentInterface $shipment */
        $shipment = $order->get_shipments()->first();
        $command = new Choose_Shipping_Method(orderTokenValue: $order->get_token_value(), shipmentId: $shipment->get_id(), shippingMethodCode: $shipping_method->get_code());
        $this->command_bus->dispatch($command);
        $payment_method = $payment_method ?: $this->payment_method_repository->find_one_by([]);
        /** @var PaymentInterface $payment */
        $payment = $order->get_payments()->first();
        $command = new Choose_Payment_Method(orderTokenValue: $order->get_token_value(), paymentId: $payment->get_id(), paymentMethodCode: $payment_method->get_code());
        $this->command_bus->dispatch($command);
    }
}