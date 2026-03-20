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
use Sylius\Component\Core\Model\Catalog_Promotion_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Catalog_Promotion_Context implements Context
{
    public function __construct(private Repository_Interface $catalog_promotion_repository)
    {
    }
    #[Transform('/^"([^"]+)" catalog promotion$/')]
    #[Transform(':catalogPromotion')]
    public function get_catalog_promotion_by_name(string $name): Catalog_Promotion_Interface
    {
        $catalog_promotion = $this->catalog_promotion_repository->find_one_by(['name' => $name]);
        Assert::not_null($catalog_promotion, sprintf('Catalog promotion with name "%s" does not exist', $name));
        return $catalog_promotion;
    }
}