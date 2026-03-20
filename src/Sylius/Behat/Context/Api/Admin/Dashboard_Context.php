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
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Symfony\Component\Clock\Clock_Interface;
use Webmozart\Assert\Assert;
final readonly class Dashboard_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Clock_Interface $clock)
    {
    }
    #[When('I view statistics')]
    public function i_browse_statistics(): void
    {
        $this->client->index('statistics');
    }
    #[When('I view statistics for :channel channel and current year split by month')]
    #[When('I choose :channel channel')]
    #[When('I view statistics for :channel channel')]
    public function i_view_statistics_for_channel_and_year(Channel_Interface $channel): void
    {
        $this->client->index('statistics', ['channelCode' => $channel->get_code(), 'startDate' => $this->clock->now()->format('Y-01-01\T00:00:00'), 'interval' => 'month', 'endDate' => $this->clock->now()->format('Y-12-31\T23:59:59')]);
    }
    #[When('I view statistics for :channel channel and previous year split by month')]
    public function i_view_statistics_for_channel_and_previous_year(Channel_Interface $channel): void
    {
        $current_year = (int) $this->clock->now()->format('Y');
        $this->client->index('statistics', ['channelCode' => $channel->get_code(), 'startDate' => $current_year - 1 . '-01-01T00:00:00', 'interval' => 'month', 'endDate' => $current_year - 1 . '-12-31T23:59:59']);
    }
    #[When('I view statistics for :channel channel and next year split by month')]
    public function i_view_statistics_for_channel_and_next_year(Channel_Interface $channel): void
    {
        $current_year = (int) $this->clock->now()->format('Y');
        $this->client->index('statistics', ['channelCode' => $channel->get_code(), 'startDate' => $current_year + 1 . '-01-01T00:00:00', 'interval' => 'month', 'endDate' => $current_year + 1 . '-12-31T23:59:59']);
    }
    #[Then('I should see :count paid orders')]
    public function i_should_see_paid_orders(int $count): void
    {
        Assert::true($this->response_checker->has_values_in_subresource_object($this->client->get_last_response(), 'businessActivitySummary', ['paidOrdersCount' => $count]));
    }
    #[Then('I should see :number new customers( in the list)')]
    public function i_should_see_new_customers(int $count): void
    {
        Assert::true($this->response_checker->has_values_in_subresource_object($this->client->get_last_response(), 'businessActivitySummary', ['newCustomersCount' => $count]), sprintf('There should be %s new customers, but got %s.', $count, json_encode($this->response_checker->get_value($this->client->get_last_response(), 'businessActivitySummary'))));
    }
    #[Then('/^there should be total sales of ("[^"]+")$/')]
    public function there_should_be_total_sales_of(int $total_sales): void
    {
        Assert::true($this->response_checker->has_values_in_subresource_object($this->client->get_last_response(), 'businessActivitySummary', ['totalSales' => $total_sales]));
    }
    #[Then('/^the average order value should be ("[^"]+")$/')]
    public function my_average_order_value_should_be(int $average_total_value): void
    {
        Assert::true($this->response_checker->has_values_in_subresource_object($this->client->get_last_response(), 'businessActivitySummary', ['averageOrderValue' => $average_total_value]), sprintf('Average order value should be %s, but it does not.', $average_total_value));
    }
}