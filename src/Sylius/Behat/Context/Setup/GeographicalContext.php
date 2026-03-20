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
use Sylius\Component\Addressing\Converter\Country_Name_Converter_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Geographical_Context implements Context
{
    /**
     * @param FactoryInterface<CountryInterface> $countryFactory
     * @param FactoryInterface<ProvinceInterface> $provinceFactory
     * @param RepositoryInterface<CountryInterface> $countryRepository
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $country_factory, private Factory_Interface $province_factory, private Repository_Interface $country_repository, private Country_Name_Converter_Interface $country_name_converter, private Object_Manager $country_manager)
    {
    }
    #[Given('/^the store ships to "([^"]+)"$/')]
    #[Given('/^the store ships to "([^"]+)" and "([^"]+)"$/')]
    #[Given('/^the store ships to "([^"]+)", "([^"]+)" and "([^"]+)"$/')]
    public function store_ships_to(string ...$countries_names): void
    {
        foreach ($countries_names as $country_name) {
            $this->country_repository->add($this->create_country_named(trim($country_name)));
        }
    }
    #[Given('/^the store operates in "([^"]*)"$/')]
    #[Given('/^the store operates in "([^"]*)" and "([^"]*)"$/')]
    #[Given('/^the store(?:| also) has country "([^"]*)"$/')]
    public function the_store_operates_in(string ...$countries_names): void
    {
        foreach ($countries_names as $country_name) {
            $country = $this->create_country_named(trim($country_name));
            $this->shared_storage->set('country', $country);
            $this->country_repository->add($country);
        }
    }
    #[Given('/^the store has disabled country "([^"]*)"$/')]
    public function the_store_has_disabled_country(string $country_name): void
    {
        $country = $this->create_country_named(trim($country_name));
        $country->disable();
        $this->shared_storage->set('country', $country);
        $this->country_repository->add($country);
    }
    #[Given('/^(this country)(?:| also) has the "([^"]+)" province with "([^"]+)" code$/')]
    #[Given('/^(?:|the )(country "[^"]+") has the "([^"]+)" province with "([^"]+)" code$/')]
    public function the_country_has_province_with_code(Country_Interface $country, string $name, string $code): void
    {
        $province = $this->province_factory->create_new();
        $province->set_name($name);
        $province->set_code($code);
        $country->add_province($province);
        $this->shared_storage->set('province', $province);
        $this->country_manager->flush();
    }
    private function create_country_named(string $name): Country_Interface
    {
        $country = $this->country_factory->create_new();
        $country->set_code($this->country_name_converter->convert_to_code($name));
        return $country;
    }
}