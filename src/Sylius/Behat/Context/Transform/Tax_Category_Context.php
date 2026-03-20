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
use Sylius\Component\Taxation\Repository\Tax_Category_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Tax_Category_Context implements Context
{
    public function __construct(private Tax_Category_Repository_Interface $tax_category_repository)
    {
    }
    #[Transform('/^"([^"]+)" tax category$/')]
    #[Transform('/^tax category "([^"]+)"$/')]
    #[Transform(':taxCategory')]
    public function get_tax_category_by_name(string $tax_category_name)
    {
        $tax_categories = $this->tax_category_repository->find_by_name($tax_category_name);
        Assert::eq(count($tax_categories), 1, sprintf('%d tax categories has been found with name "%s".', count($tax_categories), $tax_category_name));
        return $tax_categories[0];
    }
}