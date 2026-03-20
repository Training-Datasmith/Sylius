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
namespace Sylius\Behat\Service\Helper;

use Behat\Mink\Driver\Driver_Interface;
interface Autocomplete_Helper_Interface
{
    public function search(Driver_Interface $driver, string $selector, string $search_string): mixed;
    /**
     * @return array<string>
     */
    public function get_selected_items(Driver_Interface $driver, string $selector): array;
    public function select_by_name(Driver_Interface $driver, string $selector, string $name): void;
    public function remove_by_name(Driver_Interface $driver, string $selector, string $name): void;
    public function select_by_value(Driver_Interface $driver, string $selector, string $value): void;
    public function remove_by_value(Driver_Interface $driver, string $selector, string $value): void;
    public function clear(Driver_Interface $driver, string $selector): void;
}