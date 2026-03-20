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
use Sylius\Behat\Page\Admin\Order\History_Page_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Webmozart\Assert\Assert;
final readonly class Order_History_Context implements Context
{
    public function __construct(private History_Page_Interface $history_page)
    {
    }
    #[When('I browse order\'s :order history')]
    public function i_browse_order_history(Order_Interface $order): void
    {
        $this->history_page->open(['id' => $order->get_id()]);
    }
    #[Then('there should be :count shipping address changes in the registry')]
    public function there_should_be_count_shipping_address_changes_in_the_registry(int $count): void
    {
        Assert::same($this->history_page->count_shipping_address_changes(), $count);
    }
    #[Then('there should be :count billing address changes in the registry')]
    public function there_should_be_count_billing_address_changes_in_the_registry(int $count): void
    {
        Assert::same($this->history_page->count_billing_address_changes(), $count);
    }
}