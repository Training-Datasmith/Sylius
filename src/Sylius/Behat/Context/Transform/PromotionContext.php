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
use Sylius\Component\Promotion\Repository\Promotion_Coupon_Repository_Interface;
use Sylius\Component\Promotion\Repository\Promotion_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Promotion_Context implements Context
{
    public function __construct(private Promotion_Repository_Interface $promotion_repository, private Promotion_Coupon_Repository_Interface $promotion_coupon_repository)
    {
    }
    #[Transform('/^promotion "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" promotion$/')]
    #[Transform(':promotion')]
    public function get_promotion_by_name(string $promotion_name)
    {
        $promotion = $this->promotion_repository->find_one_by(['name' => $promotion_name]);
        Assert::not_null($promotion, sprintf('Promotion with name "%s" does not exist', $promotion_name));
        return $promotion;
    }
    #[Transform('/^coupon "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" coupon$/')]
    #[Transform(':coupon')]
    public function get_promotion_coupon_by_code(string $promotion_coupon_code)
    {
        $promotion_coupon = $this->promotion_coupon_repository->find_one_by(['code' => $promotion_coupon_code]);
        Assert::not_null($promotion_coupon, sprintf('Promotion coupon with code "%s" does not exist', $promotion_coupon_code));
        return $promotion_coupon;
    }
}