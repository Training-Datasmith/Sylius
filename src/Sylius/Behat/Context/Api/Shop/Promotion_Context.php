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
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Webmozart\Assert\Assert;
final readonly class Promotion_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Shared_Storage_Interface $shared_storage, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I use coupon with code :couponCode')]
    #[When('I remove coupon from my cart')]
    public function i_use_coupon_with_code(?string $coupon_code = null): void
    {
        $this->use_coupon_code($coupon_code);
    }
    #[Then('I should be notified that the coupon is invalid')]
    public function i_should_be_notified_that_coupon_is_invalid(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 422);
        Assert::same($this->response_checker->get_error($response), 'couponCode: Coupon code is invalid.');
    }
    private function get_cart_token_value(): ?string
    {
        if ($this->shared_storage->has('cart_token')) {
            return $this->shared_storage->get('cart_token');
        }
        return null;
    }
    private function use_coupon_code(?string $coupon_code): void
    {
        $this->client->build_update_request(Resources::ORDERS, $this->get_cart_token_value());
        $this->client->set_request_data(['couponCode' => $coupon_code]);
        $this->client->update();
    }
}