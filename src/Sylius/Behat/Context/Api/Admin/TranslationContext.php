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
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Webmozart\Assert\Assert;
final readonly class Translation_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[Then('I should be notified that the locale is not available')]
    public function i_should_be_notified_that_locale_is_not_available(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Please choose one of the available locales');
    }
}