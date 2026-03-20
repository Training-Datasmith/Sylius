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
namespace Sylius\Behat\Page\Test_Plugin;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Main_Page_Interface extends Sylius_Page_Interface
{
    public function get_content(): string;
}