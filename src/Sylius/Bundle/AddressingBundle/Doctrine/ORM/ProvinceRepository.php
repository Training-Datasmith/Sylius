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
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Addressing\Repository\Province_Repository_Interface;
/**
 * @implements ProvinceRepositoryInterface<ProvinceInterface>
 */
class Province_Repository extends Entity_Repository implements Province_Repository_Interface
{
}