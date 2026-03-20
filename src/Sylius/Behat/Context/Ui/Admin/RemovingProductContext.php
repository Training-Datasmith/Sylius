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
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Product\Index_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Webmozart\Assert\Assert;
final readonly class Removing_Product_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Index_Page_Interface $index_page, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('I delete the :product product')]
    #[When('I try to delete the :product product')]
    public function i_delete_product(Product_Interface $product): void
    {
        $this->shared_storage->set('product', $product);
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['name' => $product->get_name()]);
    }
    #[When('I delete the :product product on filtered page')]
    public function i_delete_product_on_filtered_page(Product_Interface $product): void
    {
        $this->shared_storage->set('product', $product);
        $this->index_page->delete_resource_on_page(['name' => $product->get_name()]);
    }
    #[Then('/^(this product) should still exist$/')]
    public function the_product_should_still_exist(Product_Interface $product): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product->get_name()]));
    }
    #[Then('I should be notified that this product could not be deleted as it is in use by a promotion rule')]
    public function i_should_be_notified_that_this_product_could_not_be_deleted(): void
    {
        $this->notification_checker->check_notification('Cannot delete a product that is in use by a promotion rule.', Notification_Type::failure());
    }
}