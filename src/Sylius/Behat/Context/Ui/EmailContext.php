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
namespace Sylius\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Sylius\Behat\Service\Checker\Email_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Symfony\Contracts\Translation\Translator_Interface;
use Webmozart\Assert\Assert;
final readonly class Email_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Email_Checker_Interface $email_checker, private Translator_Interface $translator)
    {
    }
    #[Then('it should be sent to :recipient')]
    #[Then('the email with contact request should be sent to :recipient')]
    public function an_email_should_be_sent_to(string $recipient): void
    {
        Assert::true($this->email_checker->has_recipient($recipient));
    }
    #[Then('an email with reset token should be sent to :recipient')]
    #[Then('an email with reset token should be sent to :recipient in :localeCode locale')]
    public function an_email_with_reset_token_should_be_sent_to(string $recipient, string $locale_code = 'en_US'): void
    {
        $this->assert_email_contains_message_to($this->translator->trans('sylius.email.password_reset.reset_your_password', [], null, $locale_code), $recipient);
    }
    #[Then('an email with the :method shipment\'s confirmation for the :orderNumber order should be sent to :email')]
    public function an_email_with_shipments_confirmation_for_the_order_should_be_sent_to(string $method, string $order_number, string $recipient): void
    {
        Assert::true($this->email_checker->has_message_to(sprintf('Your order with number %s has been sent using %s.', $order_number, $method), $recipient));
    }
    #[Then(':count email(s) should be sent to :recipient')]
    public function number_of_emails_should_be_sent_to(int $count, string $recipient): void
    {
        Assert::same($this->email_checker->count_messages_to($recipient), $count);
    }
    #[Then('a welcoming email should have been sent to :recipient')]
    #[Then('a welcoming email should have been sent to :recipient in :localeCode locale')]
    public function a_welcoming_email_should_have_been_sent_to(string $recipient, string $locale_code = 'en_US'): void
    {
        $this->assert_email_contains_message_to($this->translator->trans('sylius.email.user_registration.welcome_to_our_store', [], null, $locale_code), $recipient);
    }
    #[Then('a verification email should have been sent to :recipient')]
    public function a_verification_email_should_have_been_sent_to(string $recipient): void
    {
        $this->assert_email_contains_message_to($this->translator->trans('sylius.email.user.account_verification.strategy'), $recipient);
    }
    #[Then('a welcoming email should not have been sent to :recipient')]
    public function a_welcoming_email_should_not_have_been_sent_to(string $recipient): void
    {
        $this->assert_email_does_not_contain_message_to($this->translator->trans('sylius.email.user_registration.welcome_to_our_store'), $recipient);
    }
    #[Then('an email with the confirmation of the order :order should be sent to :email')]
    #[Then('an email with the confirmation of the order :order should be sent to :email in :localeCode locale')]
    public function an_email_with_the_confirmation_of_the_order_should_be_sent_to(Order_Interface $order, string $recipient, string $locale_code = 'en_US'): void
    {
        $this->assert_email_contains_message_to(sprintf('%s %s %s', $this->translator->trans('sylius.email.order_confirmation.your_order_number', [], null, $locale_code), $order->get_number(), $this->translator->trans('sylius.email.order_confirmation.has_been_successfully_placed', [], null, $locale_code)), $recipient);
    }
    #[Then('an email with the confirmation of the order :order should not be sent to :email')]
    public function an_email_with_the_confirmation_of_the_order_should_not_be_sent_to(Order_Interface $order, string $recipient, string $locale_code = 'en_US'): void
    {
        $this->assert_email_does_not_contain_message_to(sprintf('%s %s %s', $this->translator->trans('sylius.email.order_confirmation.your_order_number', [], null, $locale_code), $order->get_number(), $this->translator->trans('sylius.email.order_confirmation.has_been_successfully_placed', [], null, $locale_code)), $recipient);
    }
    #[Then('/^an email with the summary of (order placed by "[^"]+") should be sent to him$/')]
    #[Then('/^an email with the summary of (order placed by "[^"]+") should be sent to him in ("([^"]+)" locale)$/')]
    public function an_email_with_summary_of_order_placed_by_should_be_sent_to(Order_Interface $order, string $locale_code = 'en_US'): void
    {
        $this->an_email_with_the_confirmation_of_the_order_should_be_sent_to($order, $order->get_customer()->get_email_canonical(), $locale_code);
    }
    #[Then('/^an email with shipment\'s details of (this order) should be sent to "([^"]+)"$/')]
    #[Then('/^an email with shipment\'s details of (this order) should be sent to "([^"]+)" in ("([^"]+)" locale)$/')]
    #[Then('an email with the shipment\'s confirmation of the order :order should be sent to :recipient')]
    #[Then('an email with the shipment\'s confirmation of the order :order should be sent to :recipient in :localeCode locale')]
    public function an_email_with_shipment_details_of_order_should_be_sent_to(Order_Interface $order, string $recipient, string $locale_code = 'en_US'): void
    {
        $this->assert_email_contains_message_to(sprintf('%s %s %s %s.', $this->translator->trans('sylius.email.shipment_confirmation.your_order_with_number', [], null, $locale_code), $order->get_number(), $this->translator->trans('sylius.email.shipment_confirmation.has_been_sent_using', [], null, $locale_code), $this->get_shipping_method_name($order)), $recipient);
        if ($this->shared_storage->has('tracking_code')) {
            $this->assert_email_contains_message_to($this->translator->trans('sylius.email.shipment_confirmation.you_can_check_its_location_with_the_tracking_code', ['%tracking_code%' => $this->shared_storage->get('tracking_code')], null, $locale_code), $recipient);
        }
    }
    #[Then('an email with instructions on how to reset the administrator\'s password should be sent to :recipient')]
    public function an_email_with_instructions_on_how_to_reset_the_administrators_password_should_be_sent_to(string $recipient): void
    {
        $this->assert_email_contains_message_to($this->translator->trans('sylius.email.admin_password_reset.to_reset_your_password', [], null, 'en_US'), $recipient);
    }
    #[Then(':recipient should receive no emails')]
    public function recipient_should_receive_no_emails(string $recipient): void
    {
        Assert::false($this->email_checker->has_recipient($recipient));
    }
    #[Then('only one email should have been sent to :recipient')]
    public function only_one_email_should_have_been_sent_to(string $recipient): void
    {
        Assert::eq($this->email_checker->count_messages_to($recipient), 1);
    }
    private function assert_email_contains_message_to(string $message, string $recipient): void
    {
        Assert::true($this->email_checker->has_message_to($message, $recipient));
    }
    private function assert_email_does_not_contain_message_to(string $message, string $recipient): void
    {
        Assert::false($this->email_checker->has_message_to($message, $recipient));
    }
    private function get_shipping_method_name(Order_Interface $order): string
    {
        /** @var ShipmentInterface|false $shipment */
        $shipment = $order->get_shipments()->first();
        if (false === $shipment) {
            throw new \LogicException('Order should have at least one shipment.');
        }
        return $shipment->get_method()->get_name();
    }
}