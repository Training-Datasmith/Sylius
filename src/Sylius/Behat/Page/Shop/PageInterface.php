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
namespace Sylius\Behat\Page\Shop;

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface as BasePageInterface;
interface Page_Interface extends Base_Page_Interface
{
    /**
     * @param array<string, string> $parameters
     */
    public function fill_element(string $value, string $element, array $parameters = []): void;
    /**
     * @param array<string, string> $parameters
     */
    public function get_validation_message(string $element, array $parameters = []): string;
}