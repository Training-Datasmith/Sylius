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
namespace Sylius\Bundle\Addressing_Bundle\Twig;

use Sylius\Component\Addressing\Model\Country_Interface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\Missing_Resource_Exception;
use Twig\Extension\Abstract_Extension;
use Twig\Twig_Filter;
class Country_Name_Extension extends Abstract_Extension
{
    public function get_filters(): array
    {
        return [new Twig_Filter('sylius_country_name', $this->translate_country_iso_code(...))];
    }
    public function translate_country_iso_code(Country_Interface|string|null $country, ?string $locale = null): string
    {
        $country_code = $country instanceof Country_Interface ? $country->get_code() : $country;
        if (null === $country_code) {
            return '';
        }
        try {
            $country_name = Countries::get_name($country_code, $locale);
        } catch (Missing_Resource_Exception) {
            return $country_code;
        }
        return $country_name;
    }
}