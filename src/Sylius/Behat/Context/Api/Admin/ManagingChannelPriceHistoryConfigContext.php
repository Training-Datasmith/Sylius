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
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Resource\Model\Resource_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Channel_Price_History_Config_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('/^I (enable|disable) showing the lowest price of discounted products$/')]
    public function i_enable_showing_the_lowest_price_of_discounted_products(string $visible): void
    {
        $this->client->add_request_data('channelPriceHistoryConfig', ['lowestPriceForDiscountedProductsVisible' => $visible === 'enable']);
    }
    #[When('/^I specify (-?\d+) days as the lowest price for discounted products checking period$/')]
    public function i_specify_days_as_the_lowest_price_for_discounted_products_checking_period(int $days): void
    {
        $this->client->add_request_data('channelPriceHistoryConfig', ['lowestPriceForDiscountedProductsCheckingPeriod' => $days]);
    }
    #[When('I exclude the :taxon taxon from showing the lowest price of discounted products')]
    public function i_exclude_the_taxon_from_showing_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        $this->i_exclude_the_taxons_from_showing_the_lowest_price_of_discounted_products([$taxon]);
    }
    #[When('I remove the :taxon taxon from excluded taxons from showing the lowest price of discounted products')]
    public function i_remove_the_taxon_from_excluded_taxons_from_showing_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $left_taxons = [];
        foreach ($channel->get_channel_price_history_config()->get_taxons_excluded_from_showing_lowest_price() as $excluded_taxon) {
            if ($excluded_taxon->get_id() !== $taxon->get_id()) {
                $left_taxons[] = $excluded_taxon;
            }
        }
        $this->i_exclude_the_taxons_from_showing_the_lowest_price_of_discounted_products($left_taxons);
    }
    #[When('/^I exclude the ("[^"]+" and "[^"]+" taxons) from showing the lowest price of discounted products$/')]
    public function i_exclude_the_taxons_from_showing_the_lowest_price_of_discounted_products(iterable $taxons): void
    {
        $taxons_iris = [];
        foreach ($taxons as $taxon) {
            $taxons_iris[] = $this->iri_converter->get_iri_from_resource($taxon);
        }
        $this->client->add_request_data('channelPriceHistoryConfig', ['taxonsExcludedFromShowingLowestPrice' => $taxons_iris]);
    }
    #[Then('/^the "[^"]+" channel should have the lowest price of discounted products prior to the current discount (enabled|disabled)$/')]
    public function the_channel_should_have_the_lowest_price_of_discounted_products_prior_to_the_current_discount_enabled_or_disabled(string $visible): void
    {
        Assert::same($this->get_channel_pricing_field_from_last_response('lowestPriceForDiscountedProductsVisible'), $visible === 'enabled');
    }
    #[Then('/^the "[^"]+" channel should have the lowest price for discounted products checking period set to (\d+) days$/')]
    #[Then('its lowest price for discounted products checking period should be set to :days days')]
    public function the_channel_should_have_the_lowest_price_for_discounted_products_checking_period_set_to_days(int $days): void
    {
        Assert::same($this->get_channel_pricing_field_from_last_response('lowestPriceForDiscountedProductsCheckingPeriod'), $days);
    }
    #[Then('I should be notified that the lowest price for discounted products checking period must be greater than 0')]
    public function i_should_be_notified_that_the_lowest_price_for_discounted_products_checking_period_must_be_greater_than_zero(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), 'Value must be greater than 0', 'channelPriceHistoryConfig.lowestPriceForDiscountedProductsCheckingPeriod'));
    }
    #[Then('I should be notified that the lowest price for discounted products checking period must be lower')]
    public function i_should_be_notified_that_the_lowest_price_for_discounted_products_checking_period_must_be_lower(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), 'Value must be less than 2147483647', 'channelPriceHistoryConfig.lowestPriceForDiscountedProductsCheckingPeriod'));
    }
    #[Then('/^this channel should have ("[^"]+" taxon) excluded from displaying the lowest price of discounted products$/')]
    public function this_channel_should_have_taxon_excluded_from_displaying_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        $this->this_channel_should_have_taxons_excluded_from_displaying_the_lowest_price_of_discounted_products([$taxon]);
    }
    #[Then('/^this channel should have ("([^"]+)" and "([^"]+)" taxons) excluded from displaying the lowest price of discounted products$/')]
    public function this_channel_should_have_taxons_excluded_from_displaying_the_lowest_price_of_discounted_products(iterable $taxons): void
    {
        $excluded_taxons = $this->get_channel_pricing_field_from_last_response('taxonsExcludedFromShowingLowestPrice', []);
        foreach ($taxons as $taxon) {
            Assert::true($this->is_resource_admin_iri_in_array($taxon, $excluded_taxons));
        }
    }
    #[Then('/^this channel should not have ("[^"]+" taxon) excluded from displaying the lowest price of discounted products$/')]
    public function this_channel_should_not_have_taxon_excluded_from_displaying_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        $excluded_taxons = $this->get_channel_pricing_field_from_last_response('taxonsExcludedFromShowingLowestPrice', []);
        Assert::false($this->is_resource_admin_iri_in_array($taxon, $excluded_taxons));
    }
    private function get_channel_pricing_field_from_last_response(string $field, ?array $default = null): array|bool|int|string|null
    {
        return $this->response_checker->get_value($this->client->get_last_response(), 'channelPriceHistoryConfig')[$field] ?? $default;
    }
    private function is_resource_admin_iri_in_array(Resource_Interface $resource, array $iris): bool
    {
        $iri = $this->iri_converter->get_iri_from_resource_in_section($resource, 'admin');
        return in_array($iri, $iris, true);
    }
}