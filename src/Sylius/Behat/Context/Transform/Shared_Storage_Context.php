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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
final readonly class Shared_Storage_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform('/^(it|its|theirs|them)$/')]
    public function get_latest_resource()
    {
        return $this->shared_storage->get_latest_resource();
    }
    #[Transform('/^(?:this|that|the) ([^"]+)$/')]
    public function get_resource(string $resource)
    {
        return $this->shared_storage->get(String_Inflector::name_to_code($resource));
    }
}