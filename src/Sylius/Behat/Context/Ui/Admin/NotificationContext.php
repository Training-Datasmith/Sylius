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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Sylius\Behat\Element\Admin\Notifications_Element_Interface;
use Sylius\Behat\Notification_Type;
use Webmozart\Assert\Assert;
final readonly class Notification_Context implements Context
{
    public function __construct(private Notifications_Element_Interface $notifications_element)
    {
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_it_has_been_successfully_created(): void
    {
        Assert::true($this->notifications_element->has_notification((string) Notification_Type::success(), 'has been successfully created.'));
    }
    #[Then('I should be notified that it has been successfully edited')]
    #[Then('I should be notified that it has been successfully uploaded')]
    #[Then('I should be notified that the changes have been successfully applied')]
    public function i_should_be_notified_that_it_has_been_successfully_edited(): void
    {
        Assert::true($this->notifications_element->has_notification((string) Notification_Type::success(), 'has been successfully updated.'));
    }
    #[Then('I should be notified that it :has been successfully deleted')]
    #[Then('I should be notified that they :have been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(string $has_have): void
    {
        Assert::true($this->notifications_element->has_notification((string) Notification_Type::success(), sprintf('%s been successfully deleted.', $has_have)));
    }
    #[Then('I should be notified that the removal operation has started successfully')]
    public function i_should_be_notified_that_the_removal_operation_has_started_successfully(): void
    {
        Assert::true($this->notifications_element->has_notification((string) Notification_Type::success(), 'has been requested. This process can take a while depending on the number of affected products.'));
    }
    #[Then('I should be notified that it is in use')]
    public function i_should_be_notified_that_it_is_in_use(): void
    {
        Assert::true($this->notifications_element->has_notification((string) Notification_Type::error(), 'Cannot delete'));
    }
}