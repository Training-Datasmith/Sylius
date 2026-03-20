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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Tax_Rate_Interface;
use Sylius\Component\Taxation\Model\Tax_Category_Interface;
use Sylius\Component\Taxation\Repository\Tax_Category_Repository_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Webmozart\Assert\Assert;
final readonly class Taxation_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $tax_rate_factory, private Factory_Interface $tax_category_factory, private Repository_Interface $tax_rate_repository, private Tax_Category_Repository_Interface $tax_category_repository, private Object_Manager $object_manager)
    {
    }
    #[Given('the store has :taxRateName tax rate of :taxRateAmount% for :taxCategoryName within the :zone zone')]
    #[Given('the store has :taxRateName tax rate of :taxRateAmount% for :taxCategoryName within the :zone zone with dates between :startDate and :endDate')]
    #[Given('the store has :taxRateName tax rate of :taxRateAmount% for :taxCategoryName within the :zone zone identified by the :taxRateCode code')]
    #[Given('/^the store has(?:| also) "([^"]+)" tax rate of ([^"]+)% for "([^"]+)" for the (rest of the world)$/')]
    public function store_has_tax_rate_within_zone(string $tax_rate_name, string $tax_rate_amount, string $tax_category_name, Zone_Interface $zone, ?string $tax_rate_code = null, bool $included_in_price = false, ?string $start_date = null, ?string $end_date = null): void
    {
        $this->configure_tax_rate($tax_category_name, $tax_rate_code, $tax_rate_name, $zone, $tax_rate_amount, $included_in_price, $start_date !== null ? new \DateTime($start_date) : null, $end_date !== null ? new \DateTime($end_date) : null);
    }
    #[Given('the store has :taxRateName tax rate of :taxRateAmount% for :taxCategoryName within the :zone zone ending at :endDate')]
    public function store_has_tax_rate_within_zone_ending_at(string $tax_rate_name, string $tax_rate_amount, string $tax_category_name, Zone_Interface $zone, string $end_date): void
    {
        $this->configure_tax_rate($tax_category_name, null, $tax_rate_name, $zone, $tax_rate_amount, false, null, new \DateTime($end_date));
    }
    #[Given('the store has :taxRateName tax rate of :taxRateAmount% for :taxCategoryName within the :zone zone starting at :startDate')]
    public function store_has_tax_rate_within_zone_starting_at(string $tax_rate_name, string $tax_rate_amount, string $tax_category_name, Zone_Interface $zone, string $start_date): void
    {
        $this->configure_tax_rate($tax_category_name, String_Inflector::name_to_code($tax_rate_name), $tax_rate_name, $zone, $tax_rate_amount, false, new \DateTime($start_date));
    }
    #[Given('the store has included in price :taxRateName tax rate of :taxRateAmount% for :taxCategoryName within the :zone zone')]
    public function store_has_included_in_price_tax_rate_within_zone($tax_rate_name, $tax_rate_amount, $tax_category_name, Zone_Interface $zone): void
    {
        $this->store_has_tax_rate_within_zone($tax_rate_name, $tax_rate_amount, $tax_category_name, $zone, null, true);
    }
    #[Given('the store has a tax category :name with a code :code')]
    #[Given('the store has a tax category :name')]
    #[Given('the store has a tax category :name also')]
    public function the_store_has_tax_category_with_code($name, $code = null): void
    {
        $tax_category = $this->create_tax_category($name, $code);
        $this->shared_storage->set('tax_category', $tax_category);
    }
    #[Given('the store has tax categories :firstName, :secondName and :thirdName')]
    public function the_store_has_tax_categories(string ...$names): void
    {
        foreach ($names as $name) {
            $this->the_store_has_tax_category_with_code($name);
        }
    }
    #[Given('the store does not have any categories defined')]
    public function the_store_does_not_have_any_categories_defined(): void
    {
        $tax_categories = $this->tax_category_repository->find_all();
        foreach ($tax_categories as $tax_category) {
            $this->tax_category_repository->remove($tax_category);
        }
    }
    #[Given('/^the ("[^"]+" tax rate) has changed to ([^"]+)%$/')]
    public function the_tax_rate_is_of_amount(Tax_Rate_Interface $tax_rate, $amount): void
    {
        $tax_rate->set_amount((float) $this->get_amount_from_string($amount));
        $this->object_manager->flush();
    }
    #[Given('/^(this tax rate) operates between "([^"]+)" and "([^"]+)"$/')]
    public function the_tax_rate_operates_between_dates(Tax_Rate_Interface $tax_rate, string $start_date, string $end_date): void
    {
        $tax_rate->set_start_date(new \DateTime($start_date));
        $tax_rate->set_end_date(new \DateTime($end_date));
        $this->object_manager->flush();
    }
    #[Given('the :taxRate tax rate has :calculator calculator configured')]
    public function the_tax_rate_has_calculator_configured(Tax_Rate_Interface $tax_rate, string $calculator): void
    {
        $tax_rate->set_calculator($calculator);
        $this->object_manager->flush();
    }
    /**
     * @return TaxCategoryInterface
     */
    private function get_or_create_tax_category(string $tax_category_name)
    {
        $tax_categories = $this->tax_category_repository->find_by_name($tax_category_name);
        if (empty($tax_categories)) {
            return $this->create_tax_category($tax_category_name);
        }
        Assert::eq(count($tax_categories), 1, sprintf('%d tax categories has been found with name "%s".', count($tax_categories), $tax_category_name));
        return $tax_categories[0];
    }
    /**
     * @param string $taxCategoryName
     * @param string|null $taxCategoryCode
     *
     * @return TaxCategoryInterface
     */
    private function create_tax_category($tax_category_name, $tax_category_code = null)
    {
        /** @var TaxCategoryInterface $taxCategory */
        $tax_category = $this->tax_category_factory->create_new();
        if (null === $tax_category_code) {
            $tax_category_code = $this->get_code_from_name($tax_category_name);
        }
        $tax_category->set_name($tax_category_name);
        $tax_category->set_code($tax_category_code);
        $this->tax_category_repository->add($tax_category);
        return $tax_category;
    }
    /**
     * @param string $taxRateAmount
     */
    private function get_amount_from_string($tax_rate_amount): int|float
    {
        return (int) $tax_rate_amount / 100;
    }
    private function get_code_from_name(string $tax_rate_name): string
    {
        return String_Inflector::name_to_lowercase_code($tax_rate_name);
    }
    /**
     * @param string $zoneCode
     *
     */
    private function get_code_from_name_and_zone_code(string $tax_rate_name, $zone_code): string
    {
        return $this->get_code_from_name($tax_rate_name) . '_' . strtolower($zone_code);
    }
    private function configure_tax_rate(string $tax_category_name, ?string $tax_rate_code, string $tax_rate_name, Zone_Interface $zone, string $tax_rate_amount, bool $included_in_price, ?\DateTimeInterface $start_date = null, ?\DateTimeInterface $end_date = null): void
    {
        $tax_category = $this->get_or_create_tax_category($tax_category_name);
        if (null === $tax_rate_code) {
            $tax_rate_code = $this->get_code_from_name_and_zone_code($tax_rate_name, $zone->get_code());
        }
        /** @var TaxRateInterface $taxRate */
        $tax_rate = $this->tax_rate_factory->create_new();
        $tax_rate->set_name($tax_rate_name);
        $tax_rate->set_code($tax_rate_code);
        $tax_rate->set_zone($zone);
        $tax_rate->set_amount((float) $this->get_amount_from_string($tax_rate_amount));
        $tax_rate->set_category($tax_category);
        $tax_rate->set_calculator('default');
        $tax_rate->set_included_in_price($included_in_price);
        $tax_rate->set_start_date($start_date);
        $tax_rate->set_end_date($end_date);
        $this->tax_rate_repository->add($tax_rate);
        $this->shared_storage->set('tax_rate', $tax_rate);
    }
}