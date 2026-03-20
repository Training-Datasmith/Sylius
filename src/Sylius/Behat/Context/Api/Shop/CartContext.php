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
namespace Sylius\Behat\Context\Api\Shop;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Behat\Service\Sprintf_Response_Escaper;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final readonly class Cart_Context implements Context
{
    public function __construct(private Api_Client_Interface $shop_client, private Api_Client_Interface $admin_client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Product_Variant_Resolver_Interface $product_variant_resolver, private Iri_Converter_Interface $iri_converter, private Request_Factory_Interface $request_factory, private string $api_url_prefix, private Order_Repository_Interface $order_repository)
    {
    }
    #[When('/^I clear my (cart)$/')]
    public function i_clear_my_cart(string $token_value): void
    {
        $this->shop_client->delete(Resources::ORDERS, $token_value);
        $this->shared_storage->remove('cart_token');
    }
    #[When('/^I see the summary of my (cart)$/')]
    #[When('/^the visitor try to see the summary of ((?:visitor|customer)\'s cart)$/')]
    #[When('/^the (?:visitor|customer) see the summary of ((?:|their )cart)$/')]
    public function i_see_the_summary_of_my_cart(?string $token_value): void
    {
        if ($token_value === null) {
            $token_value = $this->pickup_cart();
        }
        $this->shop_client->show(Resources::ORDERS, $token_value);
    }
    #[When('/^I see the summary of my (previous cart)$/')]
    public function i_see_the_summary_of_my_previous_cart(Order_Interface $cart): void
    {
        $this->shop_client->show(Resources::ORDERS, $cart->get_token_value());
    }
    #[When('/^the administrator try to see the summary of ((?:visitor|customer)\'s cart)$/')]
    public function the_administrator_try_to_see_the_summary_of_cart(?string $token_value): void
    {
        $this->admin_client->show(Resources::ORDERS, $token_value);
    }
    #[When('/^I add(?:| the) (this product) to the (cart)$/')]
    #[When('/^I add(?:| the) ("[^"]+" product) to the (cart)$/')]
    #[When('/^I add(?:| the) (product "[^"]+") to the (cart)$/')]
    #[When('/^the (?:visitor|customer) adds(?:| the) ("[^"]+" product) to the (cart)$/')]
    public function i_add_this_product_to_the_cart(Product_Interface $product, ?string $token_value): void
    {
        $this->put_product_to_cart($product, $token_value);
        $this->shared_storage->set('product', $product);
    }
    #[When('/^I add (products "([^"]+)" and "([^"]+)") to the cart$/')]
    #[When('/^I add (products "([^"]+)", "([^"]+)" and "([^"]+)") to the cart$/')]
    public function i_add_multiple_products_to_the_cart(array $products): void
    {
        $token_value = $this->pickup_cart();
        foreach ($products as $product) {
            $this->put_product_to_cart($product, $token_value);
        }
    }
    #[When('/^I add (\d+) of (them) to (?:the|my) (cart)$/')]
    #[When('/^I add(?:| again) (\d+) (products "[^"]+") to the (cart)$/')]
    #[When('/^I try to add (\d+) (products "[^"]+") to the (cart)$/')]
    public function i_add_of_them_to_my_cart(int $quantity, Product_Interface $product, ?string $token_value): void
    {
        $this->put_product_to_cart($product, $token_value, $quantity);
        $this->shared_storage->set('product', $product);
    }
    #[When('/^I add ("[^"]+" variant) of (this product) to the (cart)$/')]
    #[When('/^I add ("[^"]+" variant) of (product "[^"]+") to the (cart)$/')]
    public function i_add_variant_of_this_product_to_the_cart(Product_Variant_Interface $product_variant, Product_Interface $product, ?string $token_value): void
    {
        $this->put_product_variant_to_cart($product_variant, $token_value, 1);
        $this->shared_storage->set('variant', $product_variant);
    }
    #[When('I add :product with :productOption :productOptionValue to the cart')]
    public function i_add_this_product_with_to_the_cart(Product_Interface $product, string $product_option, string $product_option_value): void
    {
        $product_data = json_decode($this->shop_client->show(Resources::PRODUCTS, $product->get_code())->get_content(), true, 512, \JSON_THROW_ON_ERROR);
        $variant_iri = null;
        foreach ($product_data['options'] as $option_iri) {
            $option_data = json_decode($this->shop_client->show_by_iri($option_iri)->get_content(), true, 512, \JSON_THROW_ON_ERROR);
            if ($option_data['name'] !== $product_option) {
                continue;
            }
            foreach ($option_data['values'] as $value_iri) {
                $option_value_data = json_decode($this->shop_client->show_by_iri($value_iri)->get_content(), true, 512, \JSON_THROW_ON_ERROR);
                if ($option_value_data['value'] !== $product_option_value) {
                    continue;
                }
                $this->shop_client->index(Resources::PRODUCT_VARIANTS);
                $this->shop_client->add_filter('product', $product_data['@id']);
                $this->shop_client->add_filter('optionValues', $value_iri);
                $variants_data = json_decode($this->shop_client->filter()->get_content(), true, 512, \JSON_THROW_ON_ERROR);
                Assert::same($variants_data['hydra:totalItems'], 1);
                $variant_iri = $variants_data['@id'] . '/' . $variants_data['hydra:member'][0]['code'];
            }
        }
        if (null === $variant_iri) {
            throw new \DomainException(sprintf('Could not find variant with option "%s" set to "%s"', $product_option, $product_option_value));
        }
        $token_value = $this->pickup_cart();
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_POST, 'items');
        $request->update_content(['productCode' => $product_data['code'], 'productVariant' => $variant_iri, 'quantity' => 1]);
        $this->shop_client->execute_custom_request($request);
    }
    #[Given('/^I change (product "[^"]+") quantity to (\d+)$/')]
    #[Given('I change :product quantity to :quantity')]
    #[When('/^I change (product "[^"]+") quantity to (\d+) in my (cart)$/')]
    #[When('/^the (?:visitor|customer) change (product "[^"]+") quantity to (\d+) in his (cart)$/')]
    #[When('/^the visitor try to change (product "[^"]+") quantity to (\d+) in the customer (cart)$/')]
    #[When('/^I try to change (product "[^"]+") quantity to (\d+) in my (cart)$/')]
    public function i_change_quantity_to_in_my_cart(Product_Interface $product, int $quantity, ?string $token_value = null): void
    {
        if (null === $token_value && $this->shared_storage->has('cart_token')) {
            $token_value = $this->shared_storage->get('cart_token');
        }
        $item_response = $this->get_order_item_response_from_product_in_cart($product, $token_value);
        $this->change_quantity_of_order_item((string) $item_response['id'], $quantity, $token_value);
    }
    #[When('/^I remove (product "[^"]+") from the (cart)$/')]
    public function i_remove_product_from_the_cart(Product_Interface $product, string $token_value): void
    {
        $item_response = $this->get_order_item_response_from_product_in_cart($product, $token_value);
        $this->remove_order_item_from_cart((string) $item_response['id'], $token_value);
    }
    #[When('/^I remove ("[^"]+" variant) from the (cart)$/')]
    public function i_remove_variant_from_the_cart(Product_Variant_Interface $variant, string $token_value): void
    {
        $item_response = $this->get_order_item_response_from_product_variant_in_cart($variant, $token_value);
        $this->remove_order_item_from_cart((string) $item_response['id'], $token_value);
    }
    #[When('I pick up (my )cart (again)')]
    #[When('I pick up cart in the :localeCode locale')]
    #[When('I pick up cart without specifying locale')]
    #[When('the visitor picks up the cart')]
    public function i_pick_up_my_cart(?string $locale_code = null): void
    {
        $this->pickup_cart($locale_code);
    }
    #[When('I pick up cart using wrong locale')]
    public function i_pick_up_my_cart_using_wrong_locale(): void
    {
        $this->pickup_cart('not_valid');
    }
    #[When('/^I check the details of my (cart)$/')]
    #[When('/^the visitor checks the details of their (cart)$/')]
    #[When('/^the customer checks the details of their (cart)$/')]
    #[When('/^the customer tries to check the details of their (cart)$/')]
    public function i_check_the_details_of_my_cart(string $token_value): void
    {
        $this->shop_client->show(Resources::ORDERS, $token_value);
    }
    #[When('I update my cart')]
    #[Then('I should still be on product :product page')]
    #[Then('I should be on :product product detailed page')]
    public function intentionally_left_blank(): void
    {
        // Intentionally left blank
    }
    #[Then('/^I should be notified that (this product) does not have sufficient stock$/')]
    #[Then('/^I should be notified that (this product) has insufficient stock$/')]
    #[Then('/^I should be notified that (this product) cannot be updated$/')]
    public function i_should_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->shop_client->get_last_response(), sprintf('The product variant with %s code does not have sufficient stock.', $product->get_code())));
    }
    #[Then('/^I should not be notified that (this product) does not have sufficient stock$/')]
    #[Then('/^I should not be notified that (this product) cannot be updated$/')]
    public function i_should_not_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        Assert::false($this->response_checker->has_violation_with_message($this->shop_client->get_last_response(), sprintf('The product variant with %s code does not have sufficient stock.', $product->get_code())));
    }
    #[Then('/^I should be notified that the quantity of (this product) must be between 1 and 9999$/')]
    #[Then('I should be notified that the quantity of the product :product must be between 1 and 9999')]
    public function i_should_be_notified_that_the_quantity_of_this_product_must_be_between(Product_Interface $product): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->shop_client->get_last_response(), 'Quantity must be between 1 and 9999.'));
    }
    #[Then('my cart\'s locale should be :locale')]
    public function my_cart_locale_should_be(Locale_Interface $locale): void
    {
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'localeCode'), $locale->get_code());
    }
    #[Then('/^I should not have access to the summary of my (previous cart)$/')]
    public function i_should_not_have_access_to_the_summary_of_my_cart(Order_Interface $order): void
    {
        Assert::same($this->shop_client->show(Resources::ORDERS, $order->get_token_value())->get_status_code(), Response::HTTP_NOT_FOUND, 'The access to the summary of the previous cart should be forbidden.');
    }
    #[Then('my cart should be cleared')]
    public function my_cart_should_be_cleared(): void
    {
        $response = $this->shop_client->get_last_response();
        Assert::true($this->response_checker->is_deletion_successful($response), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Cart has not been created.', $response));
    }
    #[Then('/^my (cart)\'s total should be ("[^"]+")$/')]
    #[Then('/^my (cart) total should be ("[^"]+")$/')]
    #[Then('/^the (cart) total should be ("[^"]+")$/')]
    public function my_cart_total_should_be(string $token_value, int $total): void
    {
        $response = $this->shop_client->show(Resources::ORDERS, $token_value);
        $response_total = $this->response_checker->get_value($response, 'total');
        Assert::same($total, (int) $response_total, 'Expected totals are not the same. Received message:' . $response->get_content());
    }
    #[Then('/^my (cart) items total should be ("[^"]+")$/')]
    public function my_cart_items_total_should_be(string $token_value, int $total): void
    {
        $response = $this->shop_client->show(Resources::ORDERS, $token_value);
        $response_total = $this->response_checker->get_value($response, 'itemsSubtotal');
        Assert::same($total, (int) $response_total, 'Expected items totals are not the same. Received message:' . $response->get_content());
    }
    #[Then('/^my included in price taxes should be ("[^"]+")$/')]
    public function my_included_in_price_taxes_should_be(int $tax_total): void
    {
        $response = $this->shop_client->get_last_response();
        Assert::same($this->response_checker->get_value($response, 'taxIncludedTotal'), $tax_total, Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Expected totals are not the same.', $response));
    }
    #[Then('my cart should be empty')]
    public function my_cart_should_be_empty(): void
    {
        $token_value = $this->shared_storage->get('cart_token');
        Assert::is_empty($this->response_checker->get_value($this->shop_client->show(Resources::ORDERS, $token_value), 'items'), 'Cart is not empty.');
    }
    #[Then('/^the visitor has no access to (customer\'s cart)$/')]
    public function the_visitor_has_no_access_to_customer(?string $token_value): void
    {
        $response = $this->shop_client->show(Resources::ORDERS, $token_value);
        Assert::false($this->response_checker->is_show_successful($response), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Cart has not been created.', $response));
    }
    #[Then('I should be on my cart summary page')]
    public function i_should_be_on_my_cart_summary_page(): void
    {
        // Intentionally left blank
    }
    #[Then('I should be notified that the product has been successfully added')]
    public function i_should_be_notified_that_the_product_has_been_successfully_added(): void
    {
        $response = $this->shop_client->get_last_response();
        Assert::true($this->response_checker->is_creation_successful($response), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Item has not been added.', $response));
    }
    #[Then('I should be notified that quantity of added product cannot be lower that 1')]
    public function i_should_be_notified_that_quantity_of_added_product_cannot_be_lower_than1(): void
    {
        $response = $this->shop_client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Quantity of an order item cannot be lower than 1.', $response));
    }
    #[Then('/^I should see(?:| also) "([^"]+)" with unit price ("[^"]+") in my cart$/')]
    public function i_should_see_product_with_unit_price_in_my_cart(string $product_name, int $unit_price): void
    {
        $response = $this->shop_client->get_last_response();
        foreach ($this->response_checker->get_value($response, 'items') as $item) {
            if ($item['productName'] === $product_name) {
                Assert::same($item['unitPrice'], $unit_price);
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The product %s does not exist', $product_name));
    }
    #[Then('/^I should see(?:| also) "([^"]+)" with discounted unit price ("[^"]+") in my cart$/')]
    #[Then('/^the product "([^"]+)" should have discounted unit price ("[^"]+") in the cart$/')]
    public function i_should_see_product_with_discounted_unit_price_in_my_cart(string $product_name, int $discounted_unit_price): void
    {
        $response = $this->shop_client->get_last_response();
        foreach ($this->response_checker->get_value($response, 'items') as $item) {
            if ($item['productName'] === $product_name) {
                Assert::same($item['discountedUnitPrice'], $discounted_unit_price);
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The product %s does not exist', $product_name));
    }
    #[Then('/^the product "([^"]+)" should have total price ("[^"]+") in the cart$/')]
    #[Then('/^total price of "([^"]+)" item should be ("[^"]+")$/')]
    public function the_product_should_have_total_price_in_the_cart(string $product_name, int $total_price): void
    {
        $response = $this->shop_client->get_last_response();
        foreach ($this->response_checker->get_value($response, 'items') as $item) {
            if ($item['productName'] === $product_name) {
                Assert::same($item['total'], $total_price);
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The product %s does not exist', $product_name));
    }
    #[Then('there should be one item in my cart')]
    #[Then('there should be one item named :productName in my cart')]
    public function there_should_be_one_item_in_my_cart(?string $product_name = null): void
    {
        $response = $this->shop_client->get_last_response();
        $items = $this->response_checker->get_value($response, 'items');
        Assert::count($items, 1);
        if (null !== $product_name) {
            Assert::same($items[0]['productName'], $product_name);
        }
        $this->shared_storage->set('item', $items[0]);
    }
    #[Then('/^there should be (\d+) item in my (cart)$/')]
    public function there_should_count_items_in_my_cart(int $count, string $cart_token): void
    {
        $response = $this->shop_client->show(Resources::ORDERS, $cart_token);
        $items = $this->response_checker->get_value($response, 'items');
        Assert::count($items, $count);
    }
    #[Then('/^(this item) should have name "([^"]+)"$/')]
    public function this_item_should_have_name(array $item, string $product_name): void
    {
        $response = $this->get_product_for_item($item);
        Assert::true($this->response_checker->has_value($response, 'name', $product_name), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Name not found.', $response));
    }
    #[Then('/^(this item) should have variant "([^"]+)"$/')]
    public function this_item_should_have_variant(array $item, string $variant_name): void
    {
        $response = $this->get_product_variant_for_item($item);
        Assert::true($this->response_checker->has_value($response, 'name', $variant_name), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Name not found.', $response));
    }
    #[Then('/^(this item) should have code "([^"]+)"$/')]
    public function this_item_should_have_code(array $item, string $variant_code): void
    {
        $response = $this->get_product_variant_for_item($item);
        Assert::true($this->response_checker->has_value($response, 'code', $variant_code), Sprintf_Response_Escaper::provide_message_with_escaped_response_content('Name not found.', $response));
    }
    #[Then('/^(its) price should be decreased by ("[^"]+")$/')]
    #[Then('/^(product "[^"]+") price should be decreased by ("[^"]+")$/')]
    #[Then('/^the subtotal price of (product "[^"]+") should be decreased by ("[^"]+")$/')]
    public function its_price_should_be_decreased_by(Product_Interface $product, int $amount): void
    {
        $pricing = $this->get_expected_price_of_product_times_quantity($product);
        $this->compare_item_price($product->get_name(), $pricing - $amount);
    }
    #[Then('/^(product "[^"]+") price should be discounted by ("[^"]+")$/')]
    public function its_price_should_be_discounted_by(Product_Interface $product, int $amount): void
    {
        $pricing = $this->get_expected_price_of_product_times_quantity($product);
        $this->compare_item_price($product->get_name(), $pricing - $amount, 'discountedUnitPrice');
    }
    #[Then('/^(its|theirs) subtotal price should be decreased by ("[^"]+")$/')]
    public function its_subtotal_price_should_be_decreased_by(Product_Interface $product, int $amount): void
    {
        $pricing = $this->get_expected_price_of_product_times_quantity($product);
        $this->compare_item_price($product->get_name(), $pricing - $amount, 'subtotal');
    }
    #[Then('product :product price should not be decreased')]
    public function product_price_should_not_be_decreased(Product_Interface $product): void
    {
        $this->compare_item_price($product->get_name(), $this->get_expected_price_of_product_times_quantity($product));
    }
    #[Then('I should see :productName with quantity :quantity in my cart')]
    #[Then('/^the (?:customer|visitor) should see product "([^"]+)" with quantity (\d+) in his cart$/')]
    public function i_should_see_with_quantity_in_my_cart(string $product_name, int $quantity): void
    {
        $this->check_product_quantity_by_customer($this->shop_client->get_last_response(), $product_name, $quantity);
    }
    #[Then('I should be informed that cart items are no longer available')]
    public function i_should_be_informed_that_cart_items_are_no_longer_available(): void
    {
        $response = $this->shared_storage->get('response') ?? $this->shop_client->get_last_response();
        Assert::same($response->get_status_code(), 404);
        Assert::same($this->response_checker->get_response_content($response)['hydra:description'], 'Not Found');
    }
    #[Then('I should be informed that I cannot change the cart items after the checkout is completed')]
    public function i_should_be_informed_that_i_cannot_change_the_cart_items_after_the_checkout_is_completed(): void
    {
        Assert::same($this->response_checker->get_error($this->shop_client->get_last_response()), 'Cannot change cart items after the checkout is completed."');
        Assert::same($this->shop_client->get_last_response()->get_status_code(), 422);
    }
    #[Then('/^the administrator should see "([^"]+)" product with quantity (\d+) in the (?:customer|visitor) cart$/')]
    public function the_administrator_should_see_product_with_quantity_in_the_cart(string $product_name, int $quantity): void
    {
        $this->check_product_quantity_by_admin($this->admin_client->get_last_response(), $product_name, $quantity);
    }
    #[Then('/^the (?:visitor|customer) should see ("[^"]+" product) in the (cart)$/')]
    public function the_visitor_should_see_product_in_the_cart(Product_Interface $product, string $token_value, int $quantity = 1): void
    {
        $this->shop_client->show(Resources::ORDERS, $token_value);
        $this->i_should_see_with_quantity_in_my_cart($product->get_name(), $quantity);
    }
    #[When('/^I check items in my (cart)$/')]
    public function i_check_items_of_my_cart(string $token_value): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_GET, 'items');
        $this->shop_client->execute_custom_request($request);
    }
    #[Then('/^my cart should have ("[^"]+") items total$/')]
    public function my_cart_should_have_items_total(int $items_total): void
    {
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'itemsTotal'), $items_total);
    }
    #[Then('/^my cart taxes should be ("[^"]+")$/')]
    public function my_cart_taxes_should_be(int $tax_total): void
    {
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'taxExcludedTotal'), $tax_total);
    }
    #[Then('/^my cart included in price taxes should be ("[^"]+")$/')]
    public function my_cart_taxes_included_in_price_should_be(int $tax_total): void
    {
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'taxIncludedTotal'), $tax_total);
    }
    #[Then('/^my cart should have (\d+) items of (product "([^"]+)")$/')]
    #[Then('/^my cart should have quantity of (\d+) items of (product "([^"]+)")$/')]
    public function my_cart_should_have_items(int $quantity, Product_Interface $product): void
    {
        $response = $this->shop_client->get_last_response();
        Assert::true($this->has_item_with_name_and_quantity($response, $product->get_name(), $quantity));
    }
    #[Then('/^my cart shipping total should be ("[^"]+")$/')]
    #[Then('I should not see shipping total for my cart')]
    #[Then('/^my cart estimated shipping cost should be ("[^"]+")$/')]
    #[Then('there should be no shipping fee')]
    #[Then('my cart shipping should be for free')]
    public function my_cart_shipping_fee_should_be(int $shipping_total = 0): void
    {
        $response = $this->shop_client->get_last_response();
        Assert::same($this->response_checker->get_value($response, 'shippingTotal'), $shipping_total);
    }
    #[Then('I should be redirected to my cart summary page')]
    public function i_should_be_redirected_to_my_cart_summary_page(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[Then('/^I should have empty (cart)$/')]
    public function i_should_have_empty_cart(string $token_value): void
    {
        $items = $this->response_checker->get_value($this->shop_client->show(Resources::ORDERS, $token_value), 'items');
        Assert::same(count($items), 0, 'There should be an empty cart');
    }
    #[Then('I should be unable to add it to the cart')]
    public function i_should_be_unable_to_add_it_to_the_cart(): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->shared_storage->get('product_variant');
        $token_value = $this->pickup_cart();
        $this->put_product_variant_to_cart($product_variant, $token_value);
        $response = $this->shop_client->get_last_response();
        Assert::same($response->get_status_code(), 422);
    }
    #[Then('/^this product should have ([^"]+) "([^"]+)"$/')]
    public function this_item_should_have_option_value(string $expected_option_name, string $expected_option_value_value): void
    {
        $item = $this->shared_storage->get('item');
        $option_values = $this->response_checker->get_value($this->shop_client->show_by_iri($item['variant']), 'optionValues');
        foreach ($option_values as $option_value_iri) {
            $option_value = $this->response_checker->get_response_content($this->shop_client->show_by_iri($option_value_iri));
            if ($option_value['value'] !== $expected_option_value_value) {
                continue;
            }
            $option = $this->response_checker->get_response_content($this->shop_client->show_by_iri($option_value['option']));
            if ($option['name'] === $expected_option_name) {
                return;
            }
        }
        throw new \DomainException(sprintf('Could not find item with option "%s" set to "%s"', $expected_option_name, $expected_option_value_value));
    }
    #[Then('/^I should see "([^"]+)" with original price ("[^"]+") in my cart$/')]
    public function i_should_see_with_original_price_in_my_cart(string $product_name, int $original_price): void
    {
        $response = $this->shop_client->get_last_response();
        foreach ($this->response_checker->get_value($response, 'items') as $item) {
            if ($item['productName'] === $product_name) {
                Assert::same($item['originalUnitPrice'], $original_price);
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The product %s does not exist', $product_name));
    }
    #[Then('/^I should see "([^"]+)" only with unit price ("[^"]+") in my cart$/')]
    public function i_should_see_only_with_unit_price_in_my_cart(string $product_name, int $unit_price): void
    {
        $response = $this->shop_client->get_last_response();
        foreach ($this->response_checker->get_value($response, 'items') as $item) {
            if ($item['productName'] === $product_name) {
                Assert::same($item['unitPrice'], $unit_price);
                Assert::false(isset($item['originalPrice']));
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The product %s does not exist', $product_name));
    }
    private function pickup_cart(?string $locale_code = null): string
    {
        $request = $this->request_factory->custom(sprintf('%s/shop/orders', $this->api_url_prefix), Http_Request::METHOD_POST, ['HTTP_ACCEPT_LANGUAGE' => $locale_code ?? '']);
        $this->shop_client->execute_custom_request($request);
        $token_value = $this->response_checker->get_value($this->shop_client->get_last_response(), 'tokenValue');
        $this->shared_storage->set('cart_token', $token_value);
        $this->shared_storage->set('created_as_guest', $this->response_checker->get_value($this->shop_client->get_last_response(), 'customer') === null);
        $this->shared_storage->set('order', $this->order_repository->find_one_by(['tokenValue' => $token_value]));
        return $token_value;
    }
    private function put_product_to_cart(Product_Interface $product, ?string $token_value, int $quantity = 1): void
    {
        // Hotfix for a bug that allowed a guest to add a product to the cart belonging to a logged-in user
        $has_token = $this->shared_storage->has('token');
        $created_as_guest = $this->shared_storage->has('created_as_guest') ? $this->shared_storage->get('created_as_guest') : null;
        if (!$has_token && $created_as_guest === false) {
            $token_value = null;
        }
        $token_value ??= $this->pickup_cart();
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_POST, 'items');
        $request->update_content(['productVariant' => $this->iri_converter->get_iri_from_resource($this->product_variant_resolver->get_variant($product)), 'quantity' => $quantity]);
        $this->shop_client->execute_custom_request($request);
    }
    private function put_product_variant_to_cart(Product_Variant_Interface $product_variant, ?string $token_value, int $quantity = 1): void
    {
        $token_value ??= $this->pickup_cart();
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_POST, 'items');
        $request->update_content(['productVariant' => $this->iri_converter->get_iri_from_resource($product_variant), 'quantity' => $quantity]);
        $this->shop_client->execute_custom_request($request);
    }
    private function remove_order_item_from_cart(string $order_item_id, string $token_value): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_DELETE, sprintf('items/%s', $order_item_id));
        $this->shop_client->execute_custom_request($request);
    }
    private function get_product_for_item(array $item): Response
    {
        if (!isset($item['variant'])) {
            throw new \InvalidArgumentException('Expected array to have variant key and variant to have product, but one these keys is missing. Current array: ' . serialize($item));
        }
        $response = $this->shop_client->show_by_iri(urldecode($item['variant']));
        return $this->shop_client->show_by_iri(urldecode((string) $this->response_checker->get_value($response, 'product')));
    }
    private function get_product_variant_for_item(array $item): Response
    {
        if (!isset($item['variant'])) {
            throw new \InvalidArgumentException('Expected array to have variant key and variant to have product, but one these keys is missing. Current array: ' . serialize($item));
        }
        $request = $this->request_factory->custom($item['variant'], Http_Request::METHOD_GET);
        $this->shop_client->execute_custom_request($request);
        return $this->shop_client->get_last_response();
    }
    private function get_order_item_response_from_product_in_cart(Product_Interface $product, string $token_value): ?array
    {
        $items = $this->response_checker->get_value($this->shop_client->show(Resources::ORDERS, $token_value), 'items');
        foreach ($items as $item) {
            $response = $this->get_product_for_item($item);
            if ($this->response_checker->has_value($response, 'code', $product->get_code())) {
                return $item;
            }
        }
        return null;
    }
    private function get_order_item_response_from_product_variant_in_cart(Product_Variant_Interface $variant, string $token_value): ?array
    {
        $items = $this->response_checker->get_value($this->shop_client->show(Resources::ORDERS, $token_value), 'items');
        foreach ($items as $item) {
            $response = $this->get_product_variant_for_item($item);
            if ($this->response_checker->has_value($response, 'code', $variant->get_code())) {
                return $item;
            }
        }
        return null;
    }
    private function change_quantity_of_order_item(string $order_item_id, int $quantity, string $token_value): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_PATCH, sprintf('items/%s', $order_item_id));
        $request->update_content(['quantity' => $quantity]);
        $this->shop_client->execute_custom_request($request);
        $this->shared_storage->set('response', $this->shop_client->get_last_response());
    }
    private function has_item_with_name_and_quantity(Response $response, string $product_name, int $quantity): bool
    {
        $items = $this->response_checker->get_collection($response);
        foreach ($items as $item) {
            if ($item['productName'] === $product_name && $item['quantity'] === $quantity) {
                return true;
            }
        }
        return false;
    }
    private function check_product_quantity_by_admin(Response $cart_response, string $product_name, int $quantity): void
    {
        $items = $this->response_checker->get_value($cart_response, 'items');
        foreach ($items as $item) {
            $product_response = $this->get_product_for_item($item);
            if ($this->response_checker->has_translation($product_response, 'en_US', 'name', $product_name)) {
                $this->assert_item_quantity($product_response, $item['quantity'], $quantity);
                return;
            }
        }
        throw new \InvalidArgumentException('Invalid item data');
    }
    private function check_product_quantity_by_customer(Response $cart_response, string $product_name, int $quantity): void
    {
        $items = $this->response_checker->get_value($cart_response, 'items');
        foreach ($items as $item) {
            $product_response = $this->get_product_for_item($item);
            if ($this->response_checker->has_value($product_response, 'name', $product_name)) {
                $this->assert_item_quantity($cart_response, $item['quantity'], $quantity);
                return;
            }
        }
        throw new \InvalidArgumentException('Invalid item data');
    }
    private function assert_item_quantity(Response $response, int $got_quantity, int $expected_quantity): void
    {
        Assert::same($got_quantity, $expected_quantity, Sprintf_Response_Escaper::provide_message_with_escaped_response_content(sprintf('Quantity did not match. Expected %s.', $expected_quantity), $response));
    }
    private function compare_item_price(string $product_name, int $product_price, string $price_type = 'total'): void
    {
        $items = $this->response_checker->get_value($this->get_cart_response(), 'items');
        foreach ($items as $item) {
            if ($item['productName'] === $product_name) {
                Assert::same($item[$price_type], $product_price);
                return;
            }
        }
        throw new \InvalidArgumentException('Expected product does not exist');
    }
    private function get_expected_price_of_product_times_quantity(Product_Interface $product): int
    {
        $cart_response = $this->get_cart_response();
        $items = $this->response_checker->get_value($cart_response, 'items');
        foreach ($items as $item) {
            $product_response = $this->get_product_for_item($item);
            if ($this->response_checker->has_value($product_response, 'name', $product->get_name())) {
                $variant_for_item = $this->get_product_variant_for_item($item);
                return $this->response_checker->get_value($variant_for_item, 'price') * $item['quantity'];
            }
        }
        throw new \InvalidArgumentException(sprintf('Price for product %s had not been found', $product->get_name()));
    }
    private function get_cart_response(): Response
    {
        return $this->shop_client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
    }
}