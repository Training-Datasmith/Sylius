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
namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Repository\Payment_Request_Repository_Interface;
use Symfony\Component\Http_Foundation\Request as HTTPRequest;
use Webmozart\Assert\Assert;
final readonly class Managing_Payment_Requests_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Payment_Request_Repository_Interface $payment_request_repository, private Request_Factory_Interface $request_factory, private Shared_Security_Service_Interface $shared_security_service, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I browse payment requests of an order :order')]
    public function i_browse_orders_of_a_customer(Order_Interface $order): void
    {
        $this->client->sub_resource_index(Resources::PAYMENTS, Resources::PAYMENT_REQUESTS, (string) $order->get_last_payment()->get_id());
    }
    #[When('I view details of the payment request for the :order order')]
    public function i_view_details_of_the_payment_request_for_the_order(Order_Interface $order): void
    {
        $payment_request = $this->payment_request_repository->find_one_by(['payment' => $order->get_last_payment()]);
        $this->client->show(Resources::PAYMENT_REQUESTS, (string) $payment_request->get_hash());
    }
    #[When('I filter by the :action action')]
    public function i_filter_by_the_action(string $action): void
    {
        $this->client->add_filter('action', $action);
        $this->client->filter();
    }
    #[When('I filter by the :paymentMethod payment method')]
    public function i_filter_by_the_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->client->add_filter('method.code', $payment_method->get_code());
        $this->client->filter();
    }
    #[When('I filter by the :state state')]
    public function i_filter_by_the_state(string $state): void
    {
        $this->client->add_filter('state', $state);
        $this->client->filter();
    }
    #[Then('/^there should be (\d+) payment requests? on the list$/')]
    public function there_should_be_product_variants_on_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('it should be the payment request with action :action')]
    public function it_should_be_the_payment_request_with_action(string $action): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'action', $action));
    }
    #[Then('it should be the payment request with payment method :paymentMethod')]
    public function it_should_be_the_payment_request_with_payment_method(Payment_Method_Interface $payment_method): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'method', $this->iri_converter->get_iri_from_resource_in_section($payment_method, 'admin')));
    }
    #[Then('its method should be :paymentMethod')]
    public function its_method_should_be(Payment_Method_Interface $payment_method): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'method', $this->iri_converter->get_iri_from_resource_in_section($payment_method, 'admin')));
    }
    #[Then('/^its (action|state) should be "([^"]+)"$/')]
    public function its_action_state_should_be(string $field, string $value): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), $field, strtolower($value)));
    }
    #[Then('its payload should has empty value')]
    public function its_payload_should_has_empty_value(): void
    {
        Assert::is_empty($this->response_checker->get_value($this->client->get_last_response(), 'payload'));
    }
    #[Then('its response data should has empty value')]
    public function its_response_data_should_has_empty_value(): void
    {
        Assert::is_empty($this->response_checker->get_value($this->client->get_last_response(), 'responseData'));
    }
    #[Then('the administrator should see the payment request with action :action for :paymentMethod payment method and state :state')]
    public function administrator_should_see_the_payment_request_with_action_and_state(string $action, Payment_Method_Interface $payment_method, string $state): void
    {
        $admin_user = $this->shared_storage->get('administrator');
        /** @var OrderInterface $order */
        $this->shared_security_service->perform_action_as_admin_user($admin_user, function (): void {
            $order = $this->shared_storage->get('order');
            $request = $this->request_factory->custom('/api/v2/admin/payments/' . $order->get_last_payment()->get_id() . '/payment-requests', Http_Request::METHOD_GET, [], $this->client->get_token());
            $this->client->execute_custom_request($request);
        });
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'action', $action), sprintf('Payment request should have action %s', $action));
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'method', $this->iri_converter->get_iri_from_resource_in_section($payment_method, 'admin')), sprintf('Payment request should have payment method %s', $payment_method->get_code()));
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'state', $state), sprintf('Payment request should have state %s', $state));
    }
}