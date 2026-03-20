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
namespace Sylius\Behat\Page\Shop\Account;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Change_Password_Page_Interface extends Page_Interface
{
    public function specify_current_password(string $password): void;
    public function specify_new_password(string $password): void;
    public function specify_confirmation_password(string $password): void;
    /**
     * @throws ElementNotFoundException
     */
    public function check_validation_message_for(string $element, string $message): bool;
}