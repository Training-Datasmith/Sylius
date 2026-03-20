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
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Province_Context implements Context
{
    public function __construct(private Repository_Interface $province_repository)
    {
    }
    #[Transform('/^province "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" province$/')]
    #[Transform('/^province as "([^"]+)"$/')]
    #[Transform(':province')]
    public function get_province_by_name(string $province_name): Province_Interface
    {
        /** @var ProvinceInterface|null $province */
        $province = $this->province_repository->find_one_by(['name' => $province_name]);
        Assert::not_null($province, sprintf('Province with name "%s" does not exist', $province_name));
        return $province;
    }
    #[Transform('/^"([^"]*)" and "([^"]*)" provinces$/')]
    public function get_provinces_by_name(string ...$province_names): array
    {
        return $this->province_repository->find_by(['name' => $province_names]);
    }
}