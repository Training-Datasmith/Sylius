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
namespace Sylius\Behat\Element\Admin\Crud;

use Behat\Mink\Exception\Element_Not_Found_Exception;
interface Form_Element_Interface
{
    /**
     * @param array<string, string> $parameters
     */
    public function fill_element(string $value, string $element, array $parameters = []): void;
    /**
     * @param array<string, string> $parameters
     */
    public function get_validation_message(string $element, array $parameters = []): string;
    /**
     * @throws ElementNotFoundException
     */
    public function get_validation_errors(): string;
    public function has_form_error_alert(): bool;
}