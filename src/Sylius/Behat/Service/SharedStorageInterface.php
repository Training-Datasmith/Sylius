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
namespace Sylius\Behat\Service;

use Sylius\Behat\Exception\Shared_Storage_Element_Not_Found_Exception;
interface Shared_Storage_Interface
{
    /** @throws SharedStorageElementNotFoundException */
    public function get(string $key);
    public function has(string $key): bool;
    public function set(string $key, $resource): void;
    public function remove(string $key): void;
    public function get_latest_resource();
    /**
     * @throws \RuntimeException
     */
    public function set_clipboard(array $clipboard);
}