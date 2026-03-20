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
namespace Sylius\Bundle\Addressing_Bundle\Doctrine\ORM;

use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Entity_Repository;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Repository\Country_Repository_Interface;
/**
 * @implements CountryRepositoryInterface<CountryInterface>
 */
class Country_Repository extends Entity_Repository implements Country_Repository_Interface
{
}