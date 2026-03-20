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
namespace Sylius\Behat\Element;

class Browser_Element extends Sylius_Element implements Browser_Element_Interface
{
    public function go_back(): void
    {
        $this->get_driver()->back();
    }
    public function reset_session(): void
    {
        $this->get_session()->set_cookie('MOCKSESSID');
    }
}