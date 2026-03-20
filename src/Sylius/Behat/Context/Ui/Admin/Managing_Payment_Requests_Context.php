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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Admin\Payment\Payment_Request\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Payment\Payment_Request\Show_Page_Interface;
use Sylius\Behat\Service\Shared_Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Repository\Payment_Request_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Payment_Requests_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Show_Page_Interface $show_page, private Payment_Request_Repository_Interface $payment_request_repository, private Shared_Storage_Interface $shared_storage, private Shared_Security_Service_Interface $shared_security_service)
    {
    }
    #[When('I browse payment requests of an order :order')]
    public function i_browse_payment_requests_of_a_customer(Order_Interface $order): void
    {
        $this->index_page->open(['paymentId' => $order->get_last_payment()->get_id()]);
    }
    #[When('I view details of the payment request for the :order order')]
    public function i_view_details_of_the_payment_request_for_the_order(Order_Interface $order): void
    {
        $payment = $order->get_last_payment();
        $payment_request = $this->payment_request_repository->find_one_by(['payment' => $payment]);
        $this->show_page->open(['hash' => $payment_request->get_hash(), 'paymentId' => $payment->get_id()]);
    }
    #[When('I filter by the :action action')]
    public function i_filter_by_the_action(string $action): void
    {
        $this->index_page->choose_action_to_filter($action);
        $this->index_page->filter();
    }
    #[When('I filter by the :paymentMethod payment method')]
    public function i_filter_by_the_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->index_page->choose_payment_method_to_filter($payment_method->get_name());
        $this->index_page->filter();
    }
    #[When('I filter by the :state state')]
    public function i_filter_by_the_state(string $state): void
    {
        $this->index_page->choose_state_to_filter($state);
        $this->index_page->filter();
    }
    #[Then('/^there should be (\d+) payment requests? on the list$/')]
    public function there_should_be_product_variants_on_the_list(int $count): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('it should be the payment request with action :action')]
    public function it_should_be_the_payment_request_with_action(string $action): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['action' => $action]));
    }
    #[Then('it should be the payment request with payment method :paymentMethod')]
    public function it_should_be_the_payment_request_with_payment_method(Payment_Method_Interface $payment_method): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['method' => $payment_method->get_name()]));
    }
    #[Then('its :field should be :value')]
    public function its_field_should_be(string $field, string $value): void
    {
        Assert::same($this->show_page->get_field_text($field), $value);
    }
    #[Then('its payload should has empty value')]
    public function its_payload_should_has_empty_value(): void
    {
        Assert::same($this->show_page->get_field_text('payload'), 'null');
    }
    #[Then('its response data should has empty value')]
    public function its_response_data_should_be(): void
    {
        Assert::same($this->show_page->get_field_text('response_data'), json_encode([]));
    }
    #[Then('the administrator should see the payment request with action :action for :method payment method and state :state')]
    public function administrator_should_see_the_payment_request_with_action_and_state(string $action, string $payment_method, string $state): void
    {
        $admin_user = $this->shared_storage->get('administrator');
        $this->shared_security_service->perform_action_as_admin_user($admin_user, function (): void {
            $this->i_browse_payment_requests_of_a_customer($this->shared_storage->get('order'));
        });
        Assert::true($this->index_page->is_single_resource_on_page(['action' => $action, 'state' => $state, 'method' => $payment_method]));
    }
}