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
namespace Sylius\Behat\Service\Payment_Request\Command\Offline;

use Sylius\Bundle\Payment_Bundle\Command\Payment_Request_Hash_Aware_Interface;
use Sylius\Bundle\Payment_Bundle\Command\Payment_Request_Hash_Aware_Trait;
class Notify_Payment_Request implements Payment_Request_Hash_Aware_Interface
{
    use Payment_Request_Hash_Aware_Trait;
    public function __construct(protected ?string $hash)
    {
    }
}