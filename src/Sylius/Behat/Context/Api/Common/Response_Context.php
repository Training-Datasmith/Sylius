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
namespace Sylius\Behat\Context\Api\Common;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Webmozart\Assert\Assert;
final readonly class Response_Context implements Context
{
    public function __construct(private Response_Checker_Interface $response_checker, private Api_Client_Interface $client)
    {
    }
    #[Then('I should be notified that it has been successfully edited')]
    public function i_should_be_notified_that_it_has_been_successfully_edited(): void
    {
        Assert::true($this->response_checker->is_update_successful($this->client->get_last_response()), sprintf('Resource could not be edited: %s', $this->response_checker->get_error($this->client->get_last_response())));
    }
    #[Then('I should be notified that it has been successfully uploaded')]
    public function i_should_be_notified_that_it_has_been_successfully_uploaded(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), sprintf('Resource could not be created: %s', $this->response_checker->get_error($this->client->get_last_response())));
    }
    #[Then('I should be notified that I can no longer change payment method of this order')]
    public function i_should_be_notified_that_i_can_no_longer_change_payment_method_of_this_order(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), 'You cannot change the payment method for a cancelled order.'));
    }
}