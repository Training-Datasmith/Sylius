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
namespace Sylius\Behat\Page\Admin\Promotion;

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
interface Create_Page_Interface extends Base_Create_Page_Interface
{
    public function specify_code(string $code): void;
    public function name_it(string $name): void;
    public function get_validation_message(string $element, array $parameters = []): string;
}