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

use Sylius\Component\Addressing\Provider\Province_Naming_Provider_Interface;
use Twig\Extension\Abstract_Extension;
use Twig\Twig_Filter;
class Province_Naming_Extension extends Abstract_Extension
{
    public function __construct(private readonly Province_Naming_Provider_Interface $province_naming_provider)
    {
    }
    public function get_filters(): array
    {
        return [new Twig_Filter('sylius_province_name', $this->province_naming_provider->get_name(...)), new Twig_Filter('sylius_province_abbreviation', $this->province_naming_provider->get_abbreviation(...))];
    }
}