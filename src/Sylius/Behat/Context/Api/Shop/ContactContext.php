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

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Webmozart\Assert\Assert;
final class Contact_Context implements Context
{
    private array $content = [];
    public function __construct(private readonly Request_Factory_Interface $request_factory, private readonly Api_Client_Interface $client, private readonly Response_Checker_Interface $response_checker)
    {
    }
    #[When('I want to request contact')]
    #[When('I do not specify the email')]
    #[When('I do not specify the message')]
    public function i_want_to_request_contact(): void
    {
        //intentionally left empty
    }
    #[When('I specify the message as :message')]
    public function i_specify_the_message(string $message): void
    {
        $this->content['message'] = $message;
    }
    #[When('I specify the email as :email')]
    public function i_specify_the_email($email): void
    {
        $this->content['email'] = $email;
    }
    #[When('I( try to) send it')]
    public function i_send_it(): void
    {
        $request = $this->request_factory->create('shop', Resources::CONTACT, 'Authorization', $this->client->get_token());
        $request->set_content($this->content);
        $this->client->request($request);
    }
    #[Then('I should be notified that the contact request has been submitted successfully')]
    public function i_should_be_notified_that_the_contact_request_has_been_submitted_successfully(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 202);
    }
    #[Then('I should be notified that the email is invalid')]
    public function i_should_be_notified_that_email_is_invalid(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($this->response_checker->get_error($response), 'email: The provided email is invalid.');
    }
    #[Then('/^I should be notified that the (email|message) is required$/')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        $response = $this->client->get_last_response();
        Assert::same($this->response_checker->get_error($response), sprintf('%s: This value should not be blank.', $element));
    }
}