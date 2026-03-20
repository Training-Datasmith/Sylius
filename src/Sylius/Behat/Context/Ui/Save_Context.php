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
use Sylius\Behat\Element\Save_Element_Interface;
final readonly class Save_Context implements Context
{
    public function __construct(private Save_Element_Interface $save_element)
    {
    }
    #[When('I save my changes')]
    #[When('I try to save my changes')]
    #[When('I save my changes to the images')]
    public function i_save_my_changes(): void
    {
        $this->save_element->save_changes();
    }
}