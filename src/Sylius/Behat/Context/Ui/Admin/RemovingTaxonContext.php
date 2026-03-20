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
use Sylius\Behat\Element\Admin\Taxon\Tree_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Removing_Taxon_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Tree_Element_Interface $tree_element, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('I remove taxon named :taxon')]
    #[When('I delete taxon named :taxon')]
    #[When('I try to delete taxon named :taxon')]
    public function i_remove_taxon_named(Taxon_Interface $taxon): void
    {
        $this->create_page->open();
        $this->tree_element->delete_taxon($taxon->get_name());
    }
    #[Then('the :taxonName taxon should still exist')]
    public function the_taxon_should_still_exist(string $taxon_name): void
    {
        $this->create_page->open();
        Assert::true($this->tree_element->is_taxon_on_the_list($taxon_name));
    }
    #[Then('I should be notified that this taxon could not be deleted as it is in use by a promotion rule')]
    public function i_should_be_notified_that_this_taxon_could_not_be_deleted(): void
    {
        $this->notification_checker->check_notification('Cannot delete a taxon that is in use by a promotion rule.', Notification_Type::failure());
    }
}