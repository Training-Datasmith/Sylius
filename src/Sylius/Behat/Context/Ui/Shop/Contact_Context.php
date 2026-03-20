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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Contact\Contact_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Webmozart\Assert\Assert;
final readonly class Contact_Context implements Context
{
    public function __construct(private Contact_Page_Interface $contact_page, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('I want to request contact')]
    public function i_want_to_request_contact(): void
    {
        $this->contact_page->open();
    }
    #[When('I specify the email as :email')]
    #[When('I do not specify the email')]
    public function i_specify_the_email(string $email = ''): void
    {
        $this->contact_page->fill_element($email, 'email');
    }
    #[When('I specify the message as :message')]
    #[When('I do not specify the message')]
    public function i_specify_the_message(string $message = ''): void
    {
        $this->contact_page->fill_element($message, 'message');
    }
    #[When('I send it')]
    #[When('I try to send it')]
    public function i_send_it(): void
    {
        $this->contact_page->send();
    }
    #[Then('I should be notified that the contact request has been submitted successfully')]
    public function i_should_be_notified_that_the_contact_request_has_been_submitted_successfully(): void
    {
        $this->notification_checker->check_notification('Your contact request has been submitted successfully.', Notification_Type::success());
    }
    #[Then('/^I should be notified that the (email|message) is required$/')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::same($this->contact_page->get_validation_message($element), sprintf('Please enter your %s.', $element));
    }
    #[Then('I should be notified that the email is invalid')]
    public function i_should_be_notified_that_email_is_invalid(): void
    {
        Assert::same($this->contact_page->get_validation_message('email'), 'This email is invalid.');
    }
    #[Then('I should be notified that a problem occurred while sending the contact request')]
    public function i_should_be_notified_that_a_problem_occurred_while_sending_the_contact_request(): void
    {
        $this->notification_checker->check_notification('A problem occurred while sending the contact request. Please try again later.', Notification_Type::error());
    }
}