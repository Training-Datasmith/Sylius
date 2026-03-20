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
namespace Sylius\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Shop\Account\Order\Show_Page_Interface;
use Sylius\Behat\Page\Shop\Order\Thank_You_Page_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Thank_You_Context implements Context
{
    public function __construct(private Thank_You_Page_Interface $thank_you_page, private Show_Page_Interface $order_show_page, private Order_Repository_Interface $order_repository)
    {
    }
    #[When('I go to the change payment method page')]
    public function i_go_to_the_change_payment_method_page(): void
    {
        $this->thank_you_page->go_to_the_change_payment_method_page();
    }
    #[When('I proceed to the registration')]
    public function i_proceed_to_the_registration(): void
    {
        $this->thank_you_page->create_account();
    }
    #[Then('I should be able to access this order\'s details')]
    public function i_should_be_able_to_access_this_order_details(): void
    {
        $this->thank_you_page->go_to_order_details_in_account();
        $number = $this->order_show_page->get_number();
        Assert::same($this->order_repository->find_latest(1)[0]->get_number(), $number);
    }
    #[Then('my order should be completed successfully')]
    #[Then('I should see the thank you page')]
    #[Then('the visitor should see the thank you page')]
    #[Then('the customer should see the thank you page')]
    public function i_should_see_the_thank_you_page(): void
    {
        Assert::true($this->thank_you_page->has_thank_you_message());
    }
    #[Then('I should see the thank you page in :localeCode')]
    public function i_should_see_the_thank_you_page_in_locale($locale_code): void
    {
        Assert::false($this->thank_you_page->is_open(['_locale' => $locale_code]));
    }
    #[Then('I should not see the thank you page')]
    public function i_should_not_see_the_thank_you_page(): void
    {
        Assert::false($this->thank_you_page->is_open());
    }
    #[Then('I should be informed with :paymentMethod payment method instructions')]
    public function i_should_be_informed_with_payment_method_instructions(Payment_Method_Interface $payment_method): void
    {
        Assert::same($this->thank_you_page->get_instructions(), $payment_method->get_instructions());
    }
    #[Then('I should not see any instructions about payment method')]
    public function i_should_not_see_any_instructions_about_payment_method(): void
    {
        Assert::false($this->thank_you_page->has_instructions());
    }
    #[Then('I should not be able to change payment method')]
    public function i_should_not_be_able_to_change_my_payment_method(): void
    {
        Assert::false($this->thank_you_page->has_change_payment_method_button());
    }
    #[Then('I should be able to proceed to the registration')]
    public function i_should_be_able_to_proceed_to_the_registration(): void
    {
        Assert::true($this->thank_you_page->has_registration_button());
    }
    #[Then('I should not be able to proceed to the registration')]
    public function i_should_not_be_able_to_proceed_to_the_registration(): void
    {
        Assert::false($this->thank_you_page->has_registration_button());
    }
}