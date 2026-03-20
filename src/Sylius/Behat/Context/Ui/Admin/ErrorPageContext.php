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
use Sylius\Behat\Page\Error_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Error_Page_Context implements Context
{
    public function __construct(private Error_Page_Interface $error_page)
    {
    }
    #[Then('I should see the not found page with the link to the dashboard')]
    public function i_should_see_the_not_found_page_with_the_link_to_the_dashboard(): void
    {
        Assert::true($this->error_page->is_it_admin_not_found_page(), 'This test might require to be run without debug mode enabled.');
    }
}