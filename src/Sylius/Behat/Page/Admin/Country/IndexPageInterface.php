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
namespace Sylius\Behat\Page\Admin\Country;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
use Sylius\Component\Addressing\Model\Country_Interface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function is_country_disabled(Country_Interface $country): bool;
    public function is_country_enabled(Country_Interface $country): bool;
}