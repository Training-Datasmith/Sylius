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

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Symfony\Component\Http_Foundation\Request as HTTPRequest;
use Webmozart\Assert\Assert;
final readonly class Checkout_Complete_Context implements Context
{
    public function __construct(private Request_Factory_Interface $request_factory, private Shared_Storage_Interface $shared_storage, private Api_Client_Interface $client)
    {
    }
    #[When('I check summary of my order')]
    public function i_check_summary_of_my_order(): void
    {
        $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
    }
    #[When('I try to complete checkout')]
    #[Given('I have confirmed order')]
    public function i_confirm_my_order(): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $this->shared_storage->get('cart_token'), Http_Request::METHOD_PATCH, 'complete');
        $this->client->execute_custom_request($request);
    }
    #[Then('/^I should be informed that (this variant) has been disabled$/')]
    public function i_should_be_informed_that_this_variant_has_been_disabled(Product_Variant_Interface $product_variant): void
    {
        $last_response_content = $this->client->get_last_response()->get_content();
        Assert::string($last_response_content);
        Assert::contains($last_response_content, sprintf('The product %s is no longer available.', $product_variant->get_name()));
    }
    #[Then('my order should not be placed due to changed order total')]
    public function my_order_should_not_be_placed_due_to_changed_order_total(): void
    {
        $last_response_content = $this->client->get_last_response()->get_content();
        Assert::string($last_response_content);
        Assert::contains($last_response_content, 'Order total has changed during checkout process');
    }
}