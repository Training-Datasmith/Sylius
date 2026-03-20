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
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Order_Item_Interface;
use Sylius\Component\Core\Model\Order_Item_Unit_Interface;
use Webmozart\Assert\Assert;
final readonly class Order_Item_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I try to see one of the items from the order placed by a customer :customer')]
    public function i_try_to_see_one_of_the_items_from_the_order_placed_by_a_customer(Customer_Interface $customer): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        Assert::eq($order->get_customer(), $customer);
        /** @var OrderItemInterface $orderItem */
        $order_item = $order->get_items()->first();
        $this->client->show(Resources::ORDER_ITEMS, (string) $order_item->get_id());
    }
    #[When('I try to see one of the units from the order placed by a customer :customer')]
    public function i_try_to_see_one_of_the_units_from_the_order_placed_by_a_customer(Customer_Interface $customer): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        Assert::eq($order->get_customer(), $customer);
        /** @var OrderItemUnitInterface $orderItemUnit */
        $order_item_unit = $order->get_item_units()->first();
        $this->client->show(Resources::ORDER_ITEM_UNITS, (string) $order_item_unit->get_id());
    }
    #[Then('I should not be able to see that item')]
    public function i_should_not_be_able_to_see_that_item(): void
    {
        Assert::false($this->response_checker->is_show_successful($this->client->get_last_response()));
    }
    #[Then('I should not be able to see that unit')]
    public function i_should_not_be_able_to_see_that_unit(): void
    {
        Assert::false($this->response_checker->is_show_successful($this->client->get_last_response()));
    }
}