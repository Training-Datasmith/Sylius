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
namespace Sylius\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\DBAL\Exception\Foreign_Key_Constraint_Violation_Exception;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Promotion\Model\Promotion_Coupon_Interface;
use Sylius\Component\Promotion\Repository\Promotion_Coupon_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Promotion_Coupons_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Promotion_Coupon_Repository_Interface $coupon_repository)
    {
    }
    #[When('/^I delete ("[^"]+" coupon) related to (this promotion)$/')]
    public function i_delete_coupon(Promotion_Coupon_Interface $coupon, Promotion_Interface $promotion): void
    {
        $promotion->remove_coupon($coupon);
        $this->coupon_repository->remove($coupon);
    }
    #[When('/^I try to delete ("[^"]+" coupon) related to (this promotion)$/')]
    public function i_try_to_delete_coupon(Promotion_Coupon_Interface $coupon, Promotion_Interface $promotion): void
    {
        try {
            $promotion->remove_coupon($coupon);
            $this->coupon_repository->remove($coupon);
        } catch (Foreign_Key_Constraint_Violation_Exception $exception) {
            $this->shared_storage->set('last_exception', $exception);
        }
    }
    #[Then('/^(this coupon) should no longer exist in the coupon registry$/')]
    public function coupon_should_not_exist_in_the_registry(Promotion_Coupon_Interface $coupon): void
    {
        Assert::null($this->coupon_repository->find_one_by(['code' => $coupon->get_code()]));
    }
    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function i_should_be_notified_of_failure(): void
    {
        Assert::is_instance_of($this->shared_storage->get('last_exception'), Foreign_Key_Constraint_Violation_Exception::class);
    }
    #[Then('/^([^"]+) should still exist in the registry$/')]
    public function coupon_should_still_exist_in_the_registry(Promotion_Coupon_Interface $coupon): void
    {
        Assert::not_null($this->coupon_repository->find($coupon->get_id()));
    }
}