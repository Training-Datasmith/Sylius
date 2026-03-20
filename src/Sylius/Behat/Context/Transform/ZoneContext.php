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
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Zone_Context implements Context
{
    public function __construct(private Repository_Interface $zone_repository)
    {
    }
    #[Transform('/^"([^"]+)" zone$/')]
    #[Transform('/^zone "([^"]+)"$/')]
    #[Transform('/^zone named "([^"]+)"$/')]
    #[Transform(':zone')]
    #[Transform(':otherZone')]
    public function get_zone(string $code_or_name): Zone_Interface
    {
        $zone = $this->zone_repository->find_one_by(['code' => $code_or_name]);
        if (null !== $zone) {
            return $zone;
        }
        $zone = $this->zone_repository->find_one_by(['name' => $code_or_name]);
        Assert::not_null($zone, 'Zone does not exist.');
        return $zone;
    }
    #[Transform('/^rest of the world$/')]
    public function get_rest_of_the_world_zone(): Zone_Interface
    {
        $zone = $this->zone_repository->find_one_by(['code' => 'RoW']);
        Assert::not_null($zone, 'Rest of the world zone does not exist.');
        return $zone;
    }
}