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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Admin\Customer\Show_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class User_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private User_Repository_Interface $user_repository, private Show_Page_Interface $customer_show_page, private Home_Page_Interface $home_page)
    {
    }
    #[When('I log out')]
    public function i_log_out(): void
    {
        $this->home_page->log_out();
    }
    #[When('I delete the account of :email user')]
    public function i_delete_account(string $email): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->user_repository->find_one_by_email($email);
        $this->shared_storage->set('deleted_user', $user);
        $this->customer_show_page->open(['id' => $user->get_customer()->get_id()]);
        $this->customer_show_page->delete_account();
    }
    #[Then('the customer should have no account')]
    public function the_customer_should_have_no_account(): void
    {
        $deleted_user = $this->shared_storage->get('deleted_user');
        $this->customer_show_page->open(['id' => $deleted_user->get_customer()->get_id()]);
        Assert::false($this->customer_show_page->has_account());
    }
}