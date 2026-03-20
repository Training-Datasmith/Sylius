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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Addressing\Converter\Country_Name_Converter_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Country_Context implements Context
{
    public function __construct(private Country_Name_Converter_Interface $country_name_converter, private Repository_Interface $country_repository)
    {
    }
    #[Transform('/^country "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" country$/')]
    #[Transform('/^"([^"]+)" as shipping country$/')]
    #[Transform('/^"([^"]+)" as billing country$/')]
    #[Transform(':country')]
    #[Transform(':otherCountry')]
    public function get_country_by_name(string $country_name)
    {
        $country_code = $this->country_name_converter->convert_to_code($country_name);
        $country = $this->country_repository->find_one_by(['code' => $country_code]);
        Assert::not_null($country, sprintf('Country with name "%s" does not exist', $country_name));
        return $country;
    }
    #[Transform('/^"([^"]+)", "([^"]+)" and "([^"]+)" country$/')]
    public function get_countries_by_names(string ...$country_names): array
    {
        $country_codes = $country_names;
        array_walk($country_codes, fn(string &$item): string => $item = $this->country_name_converter->convert_to_code($item));
        return $this->country_repository->find_by(['code' => $country_codes]);
    }
    #[Transform(':countryCode')]
    public function get_country_code_by_name(string $country_name): string
    {
        return $this->country_name_converter->convert_to_code($country_name);
    }
}