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
namespace Sylius\Bundle\Addressing_Bundle;

use Sylius\Bundle\Resource_Bundle\Abstract_Resource_Bundle;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
final class Sylius_Addressing_Bundle extends Abstract_Resource_Bundle
{
    /** @return string[] */
    public function get_supported_drivers(): array
    {
        return [Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM];
    }
    protected function get_model_namespace(): string
    {
        return 'Sylius\Component\Addressing\Model';
    }
}