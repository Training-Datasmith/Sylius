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
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Crud\Index\Search_Filter_Element_Interface;
final readonly class Search_Filter_Context implements Context
{
    public function __construct(private Search_Filter_Element_Interface $search_filter_element)
    {
    }
    #[When('/^I search for [^"]+ with "([^"]+)"(?:| name| code)$/')]
    #[When('/^I search for [^"]+ by "([^"]+)"$/')]
    #[When('/^I search by "([^"]+)" [^"]+$/')]
    public function i_search_resource_with(string $phrase): void
    {
        $this->search_filter_element->search_with($phrase);
    }
}