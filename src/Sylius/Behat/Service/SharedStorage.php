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
class Shared_Storage implements Shared_Storage_Interface
{
    private array $clipboard = [];
    private ?string $latest_key = null;
    public function get(string $key)
    {
        if (!isset($this->clipboard[$key])) {
            throw new Shared_Storage_Element_Not_Found_Exception($key);
        }
        return $this->clipboard[$key];
    }
    public function has(string $key): bool
    {
        return isset($this->clipboard[$key]);
    }
    public function set(string $key, $resource): void
    {
        $this->clipboard[$key] = $resource;
        $this->latest_key = $key;
    }
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($this->clipboard[$key]);
        }
    }
    public function get_latest_resource()
    {
        if (!isset($this->clipboard[$this->latest_key])) {
            throw new \InvalidArgumentException(sprintf('There is no "%s" latest resource!', $this->latest_key));
        }
        return $this->clipboard[$this->latest_key];
    }
    public function set_clipboard(array $clipboard): void
    {
        $this->clipboard = array_merge($this->clipboard, $clipboard);
    }
}