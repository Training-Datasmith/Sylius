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
use Sylius\Behat\Context\Setup\Checkout\Address_Context;
use Sylius\Behat\Context\Setup\Checkout\Payment_Context;
use Sylius\Behat\Context\Setup\Checkout\Shipping_Context;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Api_Bundle\Command\Cart\Add_Item_To_Cart;
use Sylius\Bundle\Api_Bundle\Command\Cart\Change_Item_Quantity_In_Cart;
use Sylius\Bundle\Api_Bundle\Command\Cart\Pickup_Cart;
use Sylius\Bundle\Api_Bundle\Command\Cart\Remove_Item_From_Cart;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Update_Cart;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Order_Item_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Resource\Generator\Randomness_Generator_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
use Symfony\Component\Messenger\Stamp\Handled_Stamp;
final readonly class Cart_Context implements Context
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(private Order_Repository_Interface $order_repository, private Message_Bus_Interface $command_bus, private Product_Variant_Resolver_Interface $product_variant_resolver, private Randomness_Generator_Interface $generator, private Shared_Storage_Interface $shared_storage, private Address_Context $address_context, private Shipping_Context $shipping_context, private Payment_Context $payment_context, private string $guest_cart_token_file_path)
    {
    }
    #[Given('the customer has created empty cart')]
    public function the_customer_has_the_cart(): void
    {
        $this->pickup_cart();
    }
    #[Given('/^I added (\d+) (products "[^"]+") to the (cart)$/')]
    #[Given('/^I added (\d+) of (them) to (?:the|my) (cart)$/')]
    #[Given('/^I have(?:| added) (\d+) (product(?:|s) "[^"]+") (?:to|in) the (cart)$/')]
    public function i_added_given_quantity_of_products_to_the_cart(int $quantity, Product_Interface $product, ?string $token_value): void
    {
        $this->add_product_to_cart($product, $token_value, $quantity);
    }
    #[Given('I proceeded through the checkout process')]
    public function i_proceeded_through_the_checkout_process(): void
    {
        $this->address_context->address_cart();
        $this->shipping_context->choose_shipping_method();
        $this->payment_context->choose_payment_method();
    }
    /**
     * @param ProductInterface[] $products
     */
    #[Given('/^I added (products "([^"]+)" and "([^"]+)") to the (cart)$/')]
    #[Given('/^I added (products "([^"]+)", "([^"]+)" and "([^"]+)") to the (cart)$/')]
    public function i_added_products_and_to_the_cart(array $products, ?string $token_value): void
    {
        foreach ($products as $product) {
            $this->add_product_to_cart($product, $token_value);
        }
    }
    #[Given('/^I added (product "[^"]+") to the (cart)$/')]
    #[Given('/^I added (this product) to the (cart)$/')]
    #[Given('/^I added (this product) to the (cart) again$/')]
    #[Given('/^the visitor added (product "[^"]+") to the (cart)$/')]
    #[Given('/^the customer added (product "[^"]+") to the (cart)$/')]
    #[Given('/^I have (product "[^"]+") in the (cart)$/')]
    #[Given('/^I have (product "[^"]+") added to the (cart)$/')]
    #[Given('/^the (?:customer|visitor) has (product "[^"]+") in the (cart)$/')]
    #[When('/^the (?:customer|visitor) try to add (product "[^"]+") in the customer (cart)$/')]
    public function i_added_product_to_the_cart(Product_Interface $product, ?string $token_value): void
    {
        $this->add_product_to_cart($product, $token_value);
    }
    #[Given('/^I changed (product "[^"]+") quantity to (\d+) in my (cart)$/')]
    #[Given('/^the visitor changed (product "[^"]+") quantity to (\d+) in their (cart)$/')]
    #[Given('/^the visitor changed (this product) quantity to (\d+) in their (cart)$/')]
    public function i_changed_product_quantity_in_the_cart(Product_Interface $product, int $quantity, ?string $token_value): void
    {
        /** @var OrderInterface $cart */
        $cart = $this->shared_storage->get('order');
        $order_item_id = $cart->get_items()->filter(static fn(Order_Item_Interface $order_item): bool => $order_item->get_variant()->get_product() === $product)->first()->get_id();
        $this->command_bus->dispatch(new Change_Item_Quantity_In_Cart(orderTokenValue: $token_value, orderItemId: $order_item_id, quantity: $quantity));
    }
    #[Given('/^I added ("[^"]+" variant of product "[^"]+") to the (cart)$/')]
    #[Given('/^I have ("[^"]+" variant of product "[^"]+") in the (cart)$/')]
    #[Given('/^I have ("[^"]+" variant of this product) in the (cart)$/')]
    public function i_have_variant_of_product_in_the_cart(Product_Variant_Interface $product_variant, ?string $token_value): void
    {
        if ($token_value === null || !$this->does_cart_with_token_exist($token_value)) {
            $token_value = $this->pickup_cart();
        }
        $this->command_bus->dispatch(new Add_Item_To_Cart(orderTokenValue: $token_value, productVariantCode: $product_variant->get_code(), quantity: 1));
        $this->shared_storage->set('product', $product_variant->get_product());
        $this->shared_storage->set('variant', $product_variant);
    }
    #[Given('/^I added (product "[^"]+") with (product option "[^"]+") ([^"]+) to the (cart)$/')]
    public function i_added_product_with_option_to_the_cart(Product_Interface $product, Product_Option_Interface $product_option, string $product_option_value, ?string $token_value): void
    {
        if ($token_value === null) {
            $token_value = $this->pickup_cart($token_value);
        }
        $this->command_bus->dispatch(new Add_Item_To_Cart(orderTokenValue: $token_value, productVariantCode: $this->get_product_variant_with_product_option_and_product_option_value($product, $product_option, $product_option_value)->get_code(), quantity: 1));
    }
    #[Given('/^I removed (product "[^"]+") from the (cart)$/')]
    public function i_remove_product_from_the_cart(Product_Interface $product, string $token_value): void
    {
        /** @var OrderInterface $cart */
        $cart = $this->shared_storage->get('order');
        $item_id = $cart->get_items()->filter(static fn(Order_Item_Interface $order_item): bool => $order_item->get_variant()->get_product() === $product)->first()->get_id();
        $this->command_bus->dispatch(new Remove_Item_From_Cart(orderTokenValue: $token_value, itemId: $item_id));
    }
    #[Given('/^I removed ("[^"]+" variant) from the (cart)$/')]
    public function i_remove_variant_from_the_cart(Product_Variant_Interface $variant, string $token_value): void
    {
        /** @var OrderInterface $cart */
        $cart = $this->shared_storage->get('order');
        $item_id = $cart->get_items()->filter(static fn(Order_Item_Interface $order_item): bool => $order_item->get_variant() === $variant)->first()->get_id();
        $this->command_bus->dispatch(new Remove_Item_From_Cart(orderTokenValue: $token_value, itemId: $item_id));
    }
    #[Given('/^this (cart) has promotion applied with coupon "([^"]+)"$/')]
    public function this_cart_has_coupon_applied_with_code(?string $token_value, string $coupon_code): void
    {
        if ($token_value === null) {
            $token_value = $this->pickup_cart();
        }
        $update_cart = new Update_Cart(orderTokenValue: $token_value, couponCode: $coupon_code);
        $this->command_bus->dispatch($update_cart);
    }
    private function pickup_cart(?string $token_value = 'cart'): string
    {
        $token_value ??= $this->generator->generate_uri_safe_string(10);
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $channel_code = $channel->get_code();
        if ($this->shared_storage->has('token') && $this->shared_storage->has('user')) {
            $user = $this->shared_storage->get('user');
            if ($user instanceof Shop_User_Interface) {
                $email = $user->get_customer()->get_email();
            }
            $this->shared_storage->set('created_as_guest', false);
        } else {
            file_put_contents($this->guest_cart_token_file_path, $token_value);
            $this->shared_storage->set('created_as_guest', true);
        }
        $pickup_cart = new Pickup_Cart(channelCode: $channel_code, localeCode: $channel->get_default_locale()->get_code(), email: $email ?? null, tokenValue: $token_value);
        $message = $this->command_bus->dispatch($pickup_cart);
        $this->shared_storage->set('cart_token', $token_value);
        $this->shared_storage->set('order', $message->last(Handled_Stamp::class)->get_result());
        return $token_value;
    }
    private function get_product_variant_with_product_option_and_product_option_value(Product_Interface $product, Product_Option_Interface $product_option, string $product_option_value): ?Product_Variant_Interface
    {
        foreach ($product->get_variants() as $product_variant) {
            /** @var ProductOptionValueInterface $variantProductOptionValue */
            foreach ($product_variant->get_option_values() as $variant_product_option_value) {
                if ($variant_product_option_value->get_value() === $product_option_value && $variant_product_option_value->get_option() === $product_option) {
                    return $product_variant;
                }
            }
        }
        return null;
    }
    private function add_product_to_cart(Product_Interface $product, ?string $token_value, int $quantity = 1): void
    {
        if ($token_value === null || !$this->does_cart_with_token_exist($token_value)) {
            $token_value = $this->pickup_cart($token_value);
        }
        $this->command_bus->dispatch(new Add_Item_To_Cart(orderTokenValue: $token_value, productVariantCode: $this->product_variant_resolver->get_variant($product)->get_code(), quantity: $quantity));
        $this->shared_storage->set('product', $product);
    }
    private function does_cart_with_token_exist(string $token_value): bool
    {
        return $this->order_repository->find_cart_by_token_value($token_value) !== null;
    }
}