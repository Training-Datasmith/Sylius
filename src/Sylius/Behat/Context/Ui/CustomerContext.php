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
namespace Sylius\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Then;
use Sylius\Behat\Page\Admin\Customer\Show_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Webmozart\Assert\Assert;
final readonly class Customer_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Show_Page_Interface $customer_show_page)
    {
    }
    #[Then('I should not be able to delete it again')]
    public function i_should_not_be_able_to_delete_customer_again(): void
    {
        $customer = $this->shared_storage->get('customer');
        $this->customer_show_page->open(['id' => $customer->get_id()]);
        try {
            $this->customer_show_page->delete_account();
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new \DomainException('Delete account should throw an exception!');
    }
    #[Then('the customer with this email should still exist')]
    public function customer_should_still_exist(): void
    {
        $deleted_user = $this->shared_storage->get('deleted_user');
        $this->customer_show_page->open(['id' => $deleted_user->get_customer()->get_id()]);
        Assert::false($this->customer_show_page->has_account());
    }
}