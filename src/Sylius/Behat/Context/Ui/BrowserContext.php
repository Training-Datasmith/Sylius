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
use Behat\Step\When;
use Sylius\Behat\Element\Browser_Element_Interface;
final readonly class Browser_Context implements Context
{
    public function __construct(private Browser_Element_Interface $browser_element)
    {
    }
    #[When('I go back one page in the browser')]
    public function i_go_back_one_page_in_the_browser(): void
    {
        $this->browser_element->go_back();
    }
}