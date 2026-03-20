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
namespace Sylius\Behat\Page\Admin;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Show_To_Edit_Page_Switcher_Interface extends Sylius_Page_Interface
{
    public function has_edit_page_button(): bool;
    public function switch_to_edit_page(): void;
}