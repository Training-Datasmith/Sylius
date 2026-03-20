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
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Coupon_Context implements Context
{
    public function __construct(private Repository_Interface $coupon_repository)
    {
    }
    #[Transform('/^coupon "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" coupon$/')]
    #[Transform(':coupon')]
    public function get_coupon_by_code(string $coupon_code)
    {
        $coupon = $this->coupon_repository->find_one_by(['code' => $coupon_code]);
        Assert::not_null($coupon, sprintf('Coupon with code "%s" does not exist', $coupon_code));
        return $coupon;
    }
}