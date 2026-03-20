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
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Promotion\Model\Promotion_Interface;
use Sylius\Component\Promotion\Repository\Promotion_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Promotions_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Promotion_Repository_Interface $promotion_repository, private Object_Manager $promotion_manager)
    {
    }
    #[When('/^I delete a ("([^"]+)" promotion)$/')]
    public function i_delete_promotion(Promotion_Interface $promotion): void
    {
        $this->promotion_repository->remove($promotion);
    }
    #[When('/^I try to delete a ("([^"]+)" promotion)$/')]
    public function i_try_to_delete_promotion(Promotion_Interface $promotion): void
    {
        try {
            $this->promotion_repository->remove($promotion);
        } catch (Foreign_Key_Constraint_Violation_Exception $exception) {
            $this->shared_storage->set('last_exception', $exception);
        }
    }
    #[When('I archive the :promotion promotion')]
    public function i_archive_the_promotion(Promotion_Interface $promotion): void
    {
        $promotion->set_archived_at(new \DateTime());
        $this->promotion_manager->flush();
    }
    #[Then('/^(this promotion) should no longer exist in the promotion registry$/')]
    public function promotion_should_not_exist_in_the_registry(Promotion_Interface $promotion): void
    {
        Assert::null($this->promotion_repository->find_one_by(['code' => $promotion->get_code()]));
    }
    #[Then('promotion :promotion should still exist in the registry')]
    public function promotion_should_still_exist_in_the_registry(Promotion_Interface $promotion): void
    {
        Assert::not_null($this->promotion_repository->find($promotion->get_id()));
    }
    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function i_should_be_notified_of_failure(): void
    {
        Assert::is_instance_of($this->shared_storage->get('last_exception'), Foreign_Key_Constraint_Violation_Exception::class);
    }
    #[Then('the promotion :promotion should still exist in the registry')]
    public function the_promotion_should_still_exist_in_the_registry(Promotion_Interface $promotion): void
    {
        Assert::not_null($this->promotion_repository->find($promotion));
    }
}