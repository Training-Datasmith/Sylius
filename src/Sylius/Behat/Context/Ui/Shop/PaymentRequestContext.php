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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Page\Shop\Payment_Request\Payment_Method_Notify_Page_Interface;
use Sylius\Behat\Page\Shop\Payment_Request\Payment_Request_Notify_Page;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Model\Payment_Request_Interface;
use Sylius\Component\Payment\Repository\Payment_Request_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Payment_Request_Context implements Context
{
    public function __construct(private Payment_Request_Repository_Interface $payment_request_repository, private Payment_Method_Notify_Page_Interface $payment_method_notify_page, private Payment_Request_Notify_Page $payment_request_notify_page, private Object_Manager $object_manager)
    {
    }
    #[When('I call the payment method notify page with the code :paymentMethod')]
    public function i_call_the_payment_method_notify_page_with_the_code(string $payment_method_code): void
    {
        $this->payment_method_notify_page->open_with_client('GET', ['code' => $payment_method_code]);
    }
    #[When('/^I call the payment request notify page for this payment request$/')]
    public function i_call_the_payment_request_notify_page_for_this_payment_request(): void
    {
        /** @var PaymentRequestInterface[] $paymentRequests */
        $payment_requests = $this->payment_request_repository->find_by(['action' => Payment_Request_Interface::ACTION_NOTIFY], ['createdAt' => 'ASC'], 1);
        $payment_request = $payment_requests[0];
        $this->payment_request_notify_page->open_with_client('GET', ['hash' => $payment_request->get_hash()]);
    }
    #[Then('a payment request with action :action for payment method :paymentMethod should have state :state')]
    public function a_payment_request_with_action_for_payment_method_should_have_state(string $action, Payment_Method_Interface $payment_method, string $state): void
    {
        $this->object_manager->clear();
        // avoiding doctrine cache
        /** @var PaymentRequestInterface[] $paymentRequests */
        $payment_requests = $this->payment_request_repository->find_by(['action' => $action], ['createdAt' => 'ASC'], 1);
        Assert::count($payment_requests, 1);
        $payment_request = $payment_requests[0];
        Assert::not_null($payment_request);
        Assert::eq($payment_request->get_method()->get_id(), $payment_method->get_id());
        Assert::eq($payment_request->get_state(), $state);
    }
    #[Then('/^no payment request with "([^"]*)" action should exists$/')]
    public function no_payment_request_with_action_should_exists(string $action, ?string $state = null): void
    {
        /** @var PaymentRequestInterface[] $paymentRequests */
        $payment_requests = $this->payment_request_repository->find_by(['action' => $action], ['createdAt' => 'ASC'], 1);
        Assert::is_empty($payment_requests);
    }
    #[Given('/^the response content should be empty$/')]
    public function the_response_content_should_be_empty(): void
    {
        $response = $this->payment_method_notify_page->get_client()->get_internal_response();
        Assert::is_empty($response->get_content());
    }
    #[Given('/^the response status code should be (\d+)$/')]
    public function the_response_status_code_should_be(int $status_code): void
    {
        $response = $this->payment_method_notify_page->get_client()->get_internal_response();
        Assert::eq($response->get_status_code(), $status_code);
    }
}