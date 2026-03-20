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
namespace Sylius\Behat\Page\Admin\Crud;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Sylius_Page_Interface;
interface Create_Page_Interface extends Sylius_Page_Interface
{
    /**
     * @throws ElementNotFoundException
     */
    public function get_validation_message(string $element, array $parameters = []): string;
    /**
     * @throws ElementNotFoundException
     */
    public function create(): void;
    public function get_message_invalid_form(): string;
}