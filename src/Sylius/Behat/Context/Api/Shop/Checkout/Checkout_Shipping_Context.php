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
namespace Sylius\Behat\Context\Api\Shop\Checkout;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Order_Checkout_States;
use Sylius\Component\Core\Repository\Shipping_Method_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Shipping_Context implements Context
{
    /** @param ShippingMethodRepositoryInterface<ShippingMethodInterface> $shippingMethodRepository */
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter, private Shipping_Method_Repository_Interface $shipping_method_repository)
    {
    }
    #[When('I want to complete the shipping step')]
    #[When('I go to the shipping step')]
    #[When('the customer wants to complete the shipping step')]
    public function i_want_to_complete_the_shipping_step(): void
    {
        // Intentionally left blank, as this is a UI-specific action.
    }
    #[When('I try to select non-existing shipping method')]
    public function i_try_to_select_non_existing_shipping_method(): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        $content = $this->response_checker->get_response_content($response);
        $this->client->request_patch(uri: sprintf('orders/%s/shipments/%s', $this->shared_storage->get('cart_token'), $content['shipments'][0]['id']), body: ['shippingMethod' => '/api/v2/shop/shipping-methods/NON_EXISTING']);
    }
    #[When('I complete the shipping step with the first shipping method')]
    public function i_complete_the_shipping_step_with_the_first_shipping_method(): void
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shipping_method = $this->shipping_method_repository->find_one_by([]);
        $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        $content = $this->response_checker->get_response_content($this->client->get_last_response());
        $this->client->request_patch(uri: sprintf('orders/%s/shipments/%s', $this->shared_storage->get('cart_token'), $content['shipments'][0]['id']), body: ['shippingMethod' => $this->iri_converter->get_iri_from_resource($shipping_method)]);
    }
    #[When('I change shipping method to :shippingMethod')]
    #[When('I proceed with :shippingMethod shipping method')]
    #[When('I proceed with selecting :shippingMethod shipping method')]
    #[When('I select :shippingMethod shipping method')]
    #[When('I try to change shipping method to :shippingMethod')]
    #[When('I try to select :shippingMethod shipping method')]
    #[When('the customer has proceeded with :shippingMethod shipping method')]
    #[When('the customer proceeds with :shippingMethod shipping method')]
    #[When('the visitor has proceeded with :shippingMethod shipping method')]
    #[When('the visitor proceeds with :shippingMethod shipping method')]
    public function i_try_to_select_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->choose_shipping_method($shipping_method);
    }
    #[Then('the checkout shipping method step should be completed')]
    public function the_checkout_shipping_method_step_should_be_completed(): void
    {
        Assert::same($this->get_checkout_state(), Order_Checkout_States::STATE_SHIPPING_SELECTED);
    }
    #[Then('I should see that there is no assigned shipping method')]
    public function i_should_see_that_there_is_no_assigned_shipping_method(): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        Assert::is_empty($this->response_checker->get_value($response, 'shipments'));
    }
    #[Then('there should not be any shipping method available to choose')]
    public function there_should_not_be_any_shipping_method_available_to_choose(): void
    {
        $response = $this->client->request_get('shipping-methods');
        Assert::is_empty($this->response_checker->get_collection($response));
    }
    #[Then('I should not be able to select :shippingMethod shipping method')]
    public function i_should_not_be_able_to_select_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->choose_shipping_method($shipping_method);
        Assert::same($this->client->get_last_response()->get_status_code(), 422);
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('The shipping method %s is not available for this order. Please reselect your shipping method.', $shipping_method->get_name())));
    }
    #[Then('I should see that this shipping method is not available for this address')]
    #[Then('I should see that this shipping method is also not available for this address')]
    public function i_should_see_that_this_shipping_method_is_not_available_for_this_address(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), sprintf('The shipping method %s is not available for this order. Please reselect your shipping method.', $this->shared_storage->get('shipping_method'))), sprintf('Expected to see message that shipping method "%s" is not available. Got message: "%s".', $this->shared_storage->get('shipping_method'), $this->response_checker->get_error($this->client->get_last_response())));
    }
    #[Then('I should be informed that shipping method with code :code does not exist')]
    public function i_should_be_informed_that_shipping_method_with_code_does_not_exist(string $code): void
    {
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('The shipping method with %s code does not exist.', $code)));
    }
    public function choose_shipping_method(?Shipping_Method_Interface $shipping_method = null): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        // Lack of authorization
        if (!$this->response_checker->is_show_successful($response)) {
            return;
        }
        $content = $this->response_checker->get_response_content($response);
        $this->client->request_patch(uri: sprintf('orders/%s/shipments/%s', $this->shared_storage->get('cart_token'), $content['shipments'][0]['id']), body: ['shippingMethod' => $this->iri_converter->get_iri_from_resource($shipping_method ?? $this->shipping_method_repository->find_one_by([]))]);
    }
    private function get_checkout_state(): string
    {
        $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        $response = $this->client->get_last_response();
        return $this->response_checker->get_value($response, 'checkoutState');
    }
}