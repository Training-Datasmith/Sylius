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
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Tax_Rate_Context implements Context
{
    public function __construct(private Repository_Interface $tax_rate_repository)
    {
    }
    #[Transform(':taxRate')]
    #[Transform('/^"([^"]+)" tax rate$/')]
    public function get_tax_rate_by_name(string $tax_rate_name)
    {
        $tax_rate = $this->tax_rate_repository->find_one_by(['name' => $tax_rate_name]);
        Assert::not_null($tax_rate, sprintf('Tax rate with name "%s" does not exist', $tax_rate_name));
        return $tax_rate;
    }
}